/**
 * Roundcube Snoozed Messages Plugin
 *
 * Copyright (C) 2026, Paul Oremland
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @version 1.0.0
 * @author Paul Oremland
 * @license AGPL-3.0-or-later
 */

/**
 * Helper to format relative time.
 */
function getRelativeTime(dateStr) {
  // Roundcube sends UTC time from DB
  const date = new Date(dateStr.replace(' ', 'T') + 'Z');
  const now = new Date();
  const diffMs = date - now;
  const diffMins = Math.round(diffMs / 60000);
  const diffHours = Math.round(diffMs / 3600000);
  const diffDays = Math.round(diffMs / 86400000);

  if (diffMins < 60) {
    return `in ${diffMins} min`;
  } else if (diffHours < 24) {
    return `in ${diffHours} hours`;
  } else if (diffDays === 1) {
    return 'tomorrow';
  } else if (diffDays < 7) {
    return `in ${diffDays} days`;
  } else {
    return date.toLocaleDateString();
  }
}

/**
 * Helper to render snooze times in message list.
 */
function renderSnoozeTimes() {
  const isSnoozeFolder = rcmail.env.mailbox === rcmail.env.snooze_folder;
  if (!isSnoozeFolder) {
    return;
  }

  const snoozeData = rcmail.env.snooze_data || {};
  const messages = rcmail.env.messages || {};

  Object.keys(messages).forEach((uid) => {
    const msg = messages[uid];
    const snoozeUntil = msg.snooze_until || (snoozeData && snoozeData[uid]);
    if (snoozeUntil) {
      const $row = $('#rcmrow' + uid);
      if ($row.length) {
        renderSnoozeLabel($row, snoozeUntil);
      }
    }
  });
}

/**
 * Helper to render the actual label on a row.
 */
function renderSnoozeLabel($row, snoozeUntil) {
  // CRITICAL: DOM-based duplicate prevention is the most reliable
  if ($row.data('snooze-rendered') || $row.find('.snooze-until').length) {
    return;
  }

  const relativeTime = getRelativeTime(snoozeUntil);
  
  // Robust label retrieval
  let label = 'Snoozed until';
  if (typeof rcmail.gettext === 'function') {
    label = rcmail.gettext('snoozed_until', 'snoozed_messages');
  } else if (typeof rcmail.get_label === 'function') {
    label = rcmail.get_label('snoozed_until', 'snoozed_messages');
  }

  const $snoozeInfo = $('<span>')
    .addClass('snooze-until')
    .attr('title', `${label}: ${snoozeUntil}`)
    .text(`${label}: ${relativeTime}`);

  // For absolute positioning to work, we need a relative parent.
  // We append to the row itself, but CSS will handle the positioning.
  $row.append($snoozeInfo);
  $row.data('snooze-rendered', true);
}

if (window.rcmail) {
  rcmail.addEventListener('init', (evt) => {
    /**
     * Helper to update snooze buttons state.
     */
    const updateSnoozeStatus = () => {
      const selection = rcmail.message_list
        ? rcmail.message_list.get_selection()
        : [];
      const hasSelection = selection.length > 0;
      const isSnoozeFolder = rcmail.env.mailbox === rcmail.env.snooze_folder;
      const isInbox = rcmail.env.mailbox === 'INBOX';

      // Update commands
      rcmail.enable_command('plugin.snooze-menu', hasSelection && isInbox);
      rcmail.enable_command('plugin.snooze-action', hasSelection && isInbox);
      rcmail.enable_command(
        'plugin.unsnooze-action',
        hasSelection && isSnoozeFolder,
      );

      // Toggle visibility using classes
      if (isInbox) {
        $('.snooze').removeClass('hidden');
        $('.unsnooze').addClass('hidden');
      } else if (isSnoozeFolder) {
        $('.snooze').addClass('hidden');
        $('.unsnooze').removeClass('hidden');
      } else {
        $('.snooze').addClass('hidden');
        $('.unsnooze').addClass('hidden');
      }
    };

    /**
     * Show the custom snooze dialog.
     */
    const snoozeCustomDialog = () => {
      // Get local ISO string for default value (YYYY-MM-DDTHH:mm)
      const now = new Date();
      const offset = now.getTimezoneOffset() * 60000;
      const localISO = new Date(now.getTime() - offset)
        .toISOString()
        .slice(0, 16);

      const $dialog = $('<div>')
        .attr('id', 'snooze-custom-dialog')
        .addClass('snooze-custom-dialog')
        .append(
          $('<p>').text(rcmail.gettext('snoozed_messages.snooze_custom')),
        )
        .append(
          $('<input>').attr({
            type: 'datetime-local',
            id: 'snooze-custom-time',
            class: 'form-control snooze-custom-time',
            value: localISO,
          }),
        );

      rcmail.show_popup_dialog(
        $dialog,
        rcmail.gettext('snoozed_messages.snooze_custom'),
        [
          {
            text: rcmail.gettext('snoozed_messages.snooze'),
            class: 'mainaction snooze',
            click: function () {
              const val = $('#snooze-custom-time').val();
              if (val) {
                const selected = rcmail.message_list
                  ? rcmail.message_list.get_selection()
                  : [];

                // Convert local selection to UTC ISO string
                const utcVal = new Date(val).toISOString();

                const lock = rcmail.set_busy(true, 'loading');
                rcmail.http_post(
                  'plugin.snooze-action',
                  {
                    _uid: rcmail.uids_to_list(selected),
                    _mbox: rcmail.env.mailbox,
                    _duration: utcVal,
                  },
                  lock,
                );
              }
              $(this).dialog('close');
            },
          },
          {
            text: rcmail.gettext('cancel'),
            class: 'cancel',
            click: function () {
              $(this).dialog('close');
            },
          },
        ],
        {
          width: 400,
          modal: true,
        },
      );
    };

    // 1. Register the trigger command
    rcmail.register_command(
      'plugin.snooze-menu',
      () => {
        return false;
      },
      false,
    );

    // 2. Register the snooze menu using Elastic UI API
    if (window.UI && UI.register_menu) {
      UI.register_menu('snooze-menu', {
        button: '#rcmbtn_snooze',
        container: '#snooze-menu',
        above: false,
      });
    }

    // 3. Register the ACTUAL snooze action commands
    rcmail.register_command(
      'plugin.snooze-action',
      (prop) => {
        if (prop === 'custom') {
          snoozeCustomDialog();
          return;
        }

        const selected = rcmail.message_list
          ? rcmail.message_list.get_selection()
          : [];
        if (selected.length > 0) {
          const lock = rcmail.set_busy(true, 'loading');
          rcmail.http_post(
            'plugin.snooze-action',
            {
              _uid: rcmail.uids_to_list(selected),
              _mbox: rcmail.env.mailbox,
              _duration: prop,
            },
            lock,
          );
        }
      },
      false,
    );

    // 4. Register the unsnooze action command
    rcmail.register_command(
      'plugin.unsnooze-action',
      () => {
        const selected = rcmail.message_list
          ? rcmail.message_list.get_selection()
          : [];
        if (selected.length > 0) {
          const lock = rcmail.set_busy(true, 'loading');
          rcmail.http_post(
            'plugin.unsnooze-action',
            {
              _uid: rcmail.uids_to_list(selected),
              _mbox: rcmail.env.mailbox,
            },
            lock,
          );
        }
      },
      false,
    );

    // 5. LINK EVERY MENU ITEM BY ID
    const snoozeOptions = [
      '1hour',
      'today',
      'tomorrow',
      'weekend',
      'nextweek',
      'custom',
    ];
    snoozeOptions.forEach((opt) => {
      rcmail.register_button(
        'plugin.snooze-action',
        'rcmbtn_snooze_' + opt,
        'link',
      );
    });

    // Callback for snooze success to update UI
    rcmail.addEventListener('plugin.snooze-success', (evt) => {
      if (rcmail.message_list) {
        const uids = evt.uids || [];
        for (let i = 0; i < uids.length; i++) {
          rcmail.message_list.remove_row(uids[i], false);
        }
        rcmail.message_list.clear_selection();
        updateSnoozeStatus();
      }
    });

    if (rcmail.message_list) {
      rcmail.message_list.addEventListener('select', updateSnoozeStatus);
    }

    rcmail.addEventListener('listupdate', () => {
      updateSnoozeStatus();
      renderSnoozeTimes();
    });

    // Listen for single row insertions
    rcmail.addEventListener('insertrow', (props) => {
        const isSnoozeFolder = rcmail.env.mailbox === rcmail.env.snooze_folder;
        const snoozeData = rcmail.env.snooze_data || {};
        const snoozeUntil = snoozeData[props.uid];
        
        if (isSnoozeFolder && snoozeUntil && props.row && props.row.obj) {
            renderSnoozeLabel($(props.row.obj), snoozeUntil);
        }
    });

    // Initial check
    updateSnoozeStatus();
    renderSnoozeTimes();
  });
}
