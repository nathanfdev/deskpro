<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

return [
    'portal.forms.label_save'             => 'Save',
    'portal.forms.label_submit'           => 'Submit',
    'portal.forms.label_reset'            => 'Reset',
    'portal.forms.label_drag'             => 'Drag a file in here or',
    'portal.forms.label_drag_overlay'     => 'Drag your file in here.',
    'portal.forms.label_choose'           => 'Choose a file',
    'portal.forms.label_email'            => 'Email',
    'portal.forms.label_name'             => 'Name',
    'portal.forms.label_first_name'       => 'First Name',
    'portal.forms.label_last_name'        => 'Last Name',
    'portal.forms.label_full_name'        => 'Your Name',
    'portal.forms.label_timezone'         => 'Timezone',
    'portal.forms.label_language'         => 'Language',
    'portal.forms.label_add_attachment'   => 'Add Another Attachment',
    'portal.forms.label_title'            => 'Title',
    'portal.forms.label_subject'          => 'Subject',
    'portal.forms.label_comment'          => 'What is your comment?',
    'portal.forms.label_content'          => 'Content',
    'portal.forms.label_password'         => 'Password',
    'portal.forms.label_password_confirm' => 'Confirm',
    'portal.forms.label_message'          => 'Message',
    'portal.forms.label_department'       => 'Department',
    'portal.forms.label_category'         => 'Category',
    'portal.forms.label_priority'         => 'Priority',
    'portal.forms.label_workflow'         => 'Workflow',
    'portal.forms.label_cc'               => 'CCs',
    'portal.forms.label_select'           => 'Select...',

    'portal.forms.error_ticket_department_required' => 'A department is required',
    'portal.forms.error_ticket_department_invalid'  => 'You can\'t select a parent department',
    'portal.forms.error_ticket_subject_required'    => 'A ticket subject is required',
    'portal.forms.error_ticket_subject_length'      => 'The subject must be at least {{ limit }} characters in length',
    'portal.forms.error_ticket_msg_length'          => 'Your message must be at least {{ limit }} characters in length',
    'portal.forms.error_ticket_msg_required'        => 'You must provide a message',
    'portal.forms.error_email_required'             => 'You must provide your email address',
    'portal.forms.error_invalid_email'              => 'This email address is not valid',
    'portal.forms.error_required'                   => 'This value is required',
    'portal.forms.error_regex'                      => 'This value does not match the expected format',
    'portal.forms.error_length_too_short'           => 'This value should have {{ limit }} characters or more',
    'portal.forms.error_length_too_long'            => 'This value is too long. It should have {{ limit }} characters or less',
    'portal.forms.error_length_invalid'             => 'This value should have exactly {{ limit }} characters.',
    'portal.forms.error_date_day'                   => 'This is not a valid day of the week',
    'portal.forms.error_date_min'                   => 'This date is too far into the past. Please pick a date after {{ date }}.',
    'portal.forms.error_date_max'                   => 'This date is too far into the future. Please pick a date before {{ date }}.',
    'portal.forms.error_banned_email'               => 'Email "{{ email }}" is banned.',
    'portal.forms.error_captcha'                    => 'The CAPTCHA value was incorrect',
    'portal.forms.label_captcha'                    => 'To prove you are a human, please tell us the text you see in the CAPTCHA image',
    'portal.forms.error_csrf'                       => 'The form you submitted has expired. Please re-submit your form.',
    'portal.forms.error_upload_general'             => 'There was a problem uploading this file. Please try again.',
    'portal.forms.error_upload_file'                => 'Could not upload file',
    'portal.forms.error_upload_html_size'           => 'The file is too large.',
    'portal.forms.error_upload_ini_size'            => 'The file is too large. Maximum allowed size is {{ limit }} {{ suffix }}.',
    'portal.forms.error_upload_empty'               => 'You cannot upload an empty file.',
    'portal.forms.error_accept_size'                => 'This file is too large. Maximum allowed size is {{ detail }}.',
    'portal.forms.error_accept_failed_upload'       => 'There was a problem uploading this file. Please try again.',
    'portal.forms.error_accept_no_file'             => 'You cannot upload an empty file.',
    'portal.forms.error_accept_server_error'        => 'There was a problem uploading this file. Please try again.',
    'portal.forms.error_accept_not_allowed_exts'    => 'You cannot upload a file with any of the following file extensions: {{ detail }}',
    'portal.forms.error_accept_not_in_allowed_exts' => 'You can only upload a file with any of the following file extensions: {{ detail }}',
    'portal.forms.error_server_rejected_size'       => 'There was a problem uploading files due to the maximum size limit. Please try uploading smaller files.',
    'portal.forms.error_unique_entity'              => 'This value already exists in the system.',
    'portal.forms.error_dupe_email'                 => 'Email "{{ email }}" is already in use by other user.',

    'portal.forms.error_password_min_length'            => 'Minimum of {{count}} character|Minimum of {{count}} characters',
    'portal.forms.error_password_require_num_uppercase' => 'At least {{count}} uppercase character|At least {{count}} uppercase characters',
    'portal.forms.error_password_require_num_lowercase' => 'At least {{count}} lowercase character|At least {{count}} lowercase characters',
    'portal.forms.error_password_require_num_number'    => 'At least {{count}} number|At least {{count}} numbers',
    'portal.forms.error_password_require_num_symbol'    => 'At least {{count}} symbol|At least {{count}} symbols',
    'portal.forms.error_password_forbid_reuse'          => 'You cannot use a password you have used before',
    'portal.forms.error_password_current'               => 'This must be your current password',
    'portal.forms.extra_fields'                         => 'There was an error processing your request. Please try again.',
    'portal.forms.confirm_reset'                        => 'Are you sure you want to reset this form?',
];
