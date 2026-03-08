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
    rcmail.addEventListener('listupdate', updateSnoozeStatus);

    // Initial check
    updateSnoozeStatus();
  });
}
