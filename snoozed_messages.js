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
 * @version 1.1.2
 * @author Paul Oremland
 * @license AGPL-3.0-or-later
 */

if (!window.SNOOZE_MESSAGES_LOADED) {
  window.SNOOZE_MESSAGES_LOADED = true;

  /**
   * Helper to format relative time.
   *
   * @param {string} dateStr UTC date string from database.
   * @return {string} Formatted relative time.
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
      return 'in ' + diffMins + ' min';
    } else if (diffHours < 24) {
      return 'in ' + diffHours + ' hours';
    } else if (diffDays === 1) {
      return 'tomorrow';
    } else if (diffDays < 7) {
      return 'in ' + diffDays + ' days';
    } else {
      return date.toLocaleDateString();
    }
  }

  /**
   * Robust helper to find the row element for a UID.
   *
   * @param {string} uid Numeric or base64 UID.
   * @return {jQuery} Row element.
   */
  function getRowElement(uid) {
    // 1. Try numeric ID directly
    let $row = $('#rcmrow' + uid);
    if ($row.length) return $row;

    // 2. Try Roundcube's internal mapping (most reliable)
    if (
      rcmail.message_list &&
      rcmail.message_list.rows &&
      rcmail.message_list.rows[uid]
    ) {
      return $(rcmail.message_list.rows[uid].obj);
    }

    // 3. Try base64 fallback (common in Elastic)
    try {
      // Roundcube Elastic skin often uses base64 for row IDs to handle special characters
      let b64 = btoa(uid).replace(/=/g, '');
      $row = $('#rcmrow' + b64);
      if ($row.length) return $row;
    } catch (e) {
      // ignore
    }

    return $();
  }

  let renderTimer = null;

  /**
   * Actual rendering logic for snooze times.
   */
  function doRenderSnoozeTimes() {
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
        const $row = getRowElement(uid);
        if ($row.length) {
          renderSnoozeLabel($row, snoozeUntil, 'doRenderSnoozeTimes:' + uid);
        }
      }
    });
  }

  /**
   * Debounced helper to render snooze times in message list.
   */
  function renderSnoozeTimes() {
    if (renderTimer) {
      clearTimeout(renderTimer);
    }
    renderTimer = setTimeout(() => {
      doRenderSnoozeTimes();
      renderTimer = null;
    }, 50);
  }

  /**
   * Helper to render the actual label on a row.
   *
   * @param {jQuery} $row The message row element.
   * @param {string} snoozeUntil UTC date string.
   * @param {string} source Debug source identifier.
   */
  function renderSnoozeLabel($row, snoozeUntil, source = 'unknown') {
    $row.each(function () {
      const $thisRow = $(this);
      const rowId = $thisRow.attr('id');

      // CRITICAL: Remove ANY existing labels from the entire row first.
      $thisRow.find('.snooze-until').remove();

      // Check the entire document for a label already associated with this row ID
      // in case Roundcube is cloning elements or using multiple views.
      const globalId = 'snooze-label-' + rowId;
      $(`#${globalId}`).remove();

      const relativeTime = getRelativeTime(snoozeUntil);

      // Robust label retrieval
      let label = 'Snoozed until';
      if (typeof rcmail.gettext === 'function') {
        label = rcmail.gettext('snoozed_until', 'snoozed_messages');
      } else if (typeof rcmail.get_label === 'function') {
        label = rcmail.get_label('snoozed_until', 'snoozed_messages');
      }

      const $snoozeInfo = $('<span>')
        .attr('id', globalId)
        .addClass('snooze-until')
        .attr('title', label + ': ' + snoozeUntil)
        .text(label + ': ' + relativeTime);

      // Target the subject cell to ensure it stays in the row flow in Safari
      let $target = $thisRow.find('.subject');
      if (!$target.length) {
        $target = $thisRow;
      }

      // Using .first() in case Roundcube has multiple .subject cells in some layouts
      $target.first().append($snoozeInfo);
      $thisRow.data('snooze-rendered', true);
    });
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
          hasSelection && isSnoozeFolder
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
            $('<p>').text(rcmail.gettext('snoozed_messages.snooze_custom'))
          )
          .append(
            $('<input>').attr({
              type: 'datetime-local',
              id: 'snooze-custom-time',
              class: 'form-control snooze-custom-time',
              value: localISO,
            })
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
                    lock
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
          }
        );
      };

      // 1. Register the trigger command
      rcmail.register_command(
        'plugin.snooze-menu',
        () => {
          return false;
        },
        false
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
              lock
            );
          }
        },
        false
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
              lock
            );
          }
        },
        false
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
          'link'
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
        const messages = rcmail.env.messages || {};
        const snoozeUntil =
          (messages[props.uid] && messages[props.uid].snooze_until) ||
          snoozeData[props.uid];

        if (isSnoozeFolder && snoozeUntil && props.row && props.row.obj) {
          renderSnoozeLabel(
            $(props.row.obj),
            snoozeUntil,
            'insertrow:' + props.uid
          );
        }
      });

      // Initial check
      updateSnoozeStatus();
      renderSnoozeTimes();
    });
  }
}
