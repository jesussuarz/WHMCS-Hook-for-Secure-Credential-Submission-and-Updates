# WHMCS Hook for Secure Credential Submission and Updates

This guide provides step-by-step instructions on implementing a private credential submission button in WHMCS.

## Introduction

I spent a night coding this feature for my own project and decided to share it with the community so you can implement it in your WHMCS setup as well.

## Requirements
* WHMCS
* Twenty-One Template (Note: This has only been tested on the Twenty-One template. If you try it with another template and encounter issues, feel free to leave a comment, and I’ll do my best to help you out.)

## Installation Steps
### Step 1: Upload the Hook
Upload the customticketpage.php file to your hooks directory:
```
/includes/hooks/customticketpage.php
```

### Step 2: Modify the Support Ticket Submit Custom Fields Template
Navigate to the following file and remove all its content:
```
your_whmcs/templates/twenty-one/supportticketsubmit-customfields.tpl
```
<em>Don’t worry—this will not negatively impact your WHMCS installation. It simply prevents the custom fields from duplicating.</em>

### Step 3: Modify the View Ticket Template
Open the viewticket.tpl file located at:
```
your_whmcs/templates/twenty-one/viewticket.tpl
```

Paste the following content at the beginning of the file:
```smarty
{if $showCustomContent}
    <div class="col-12">
        {if $closedticket}
            <div class="alert alert-warning text-center">
                {lang key='supportticketclosedmsg'}
            </div>
        {/if}

        <div class="card view-ticket">
            <div class="card-body p-3">
                <h3 class="card-title">Submit/Update Login Credentials</h3>

                <p>
                    The following information will help us troubleshoot your issue more effectively. If you choose not to provide this information, it may take us longer to solve your issue.
                </p>
                <div class="alert alert-success">
                    <i class="fas fa-lock"></i>
                    All login and access information is submitted using SSL encryption and stored encrypted.
                </div>
            </div>

            <div class="card-body p-3">
                <form method="post" action="{$customFormAction}" role="form" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="{$csrfToken}">
                    <input type="hidden" name="updatecustomfields" value="1">

                    <div class="form-row">
                        {foreach from=$customfields item=customfield}
                            {if in_array($customfield.id, [3, 4, 5, 6, 7, 8, 9, 10, 11])} <!-- IDs personalizados -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="customfield{$customfield.id}">{$customfield.name}</label>

                                        {if $customfield.type == 'password' || $customfield.type == 'textarea'}
                                            {assign var="maskedValue" value=""}
                                            {assign var="length" value=$customfield.value|strlen}
                                            {for $i=0 to $length-1}
                                                {assign var="maskedValue" value=$maskedValue|cat:"*"}
                                            {/for}
                                            {if $customfield.type == 'password'}
                                                <input type="password" name="customfield[{$customfield.id}]" id="customfield{$customfield.id}" value="{$maskedValue}" class="form-control">
                                            {else}
                                                <textarea name="customfield[{$customfield.id}]" id="customfield{$customfield.id}" class="form-control">{$maskedValue}</textarea>
                                            {/if}
                                        {else}
                                            {$customfield.input}
                                        {/if}

                                        {if $customfield.description}
                                            <small class="form-text text-muted">{$customfield.description}</small>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    </div>

                    <p class="text-center">
                        <button type="submit" class="btn btn-primary" {if $closedticket}disabled="disabled"{/if}>
                            <i class="fas fa-save"></i>&nbsp;Save/Update
                        </button>
                        <a href="viewticket.php?tid={$tid}&c={$c}" class="btn btn-default">Cancel & Return to Ticket</a>
                    </p>
                </form>
                        {if $closedticket}
            <div class="alert alert-warning text-center">
The ticket is currently closed. All private data will be deleted 24 hours after the case is closed.
            </div>
        {/if}
            </div>
        </div>
    </div>
{else}
```

<strong>Note:</strong> This will not delete anything—simply paste it at the beginning, and close it at the end with:
```smarty
{/if}
```

### Step 4: Create Custom Fields
Go to:
```chrome
Your Admin > Configuration () > System Settings > Support Departments
```

Below is the configuration I used for the custom fields:
| Field Name                        | Field Type | Description        | Validation  | Dropdown Options | Admin Only | Required | Order of Appearance |
|-----------------------------------|------------|--------------------|-------------|------------------|------------|----------|---------------------|
| FTP/SFTP/Linux/RDP Hostname       | Text Field |                    |             |                  | No         | No       | 0                   |
| FTP/SFTP/Linux/RDP Username       | Text Field |                    |             |                  | No         | No       | 1                   |
| FTP/SFTP/Linux/RDP Password       | Password   |                    |             |                  | No         | No       | 2                   |
| FTP/SFTP/Linux/RDP Port           | Text Field |                    | `/^[0-9]+$/`|                  | No         | No       | 3                   |
| Control Panel URL                 | Text Field |                    |             |                  | No         | No       | 4                   |
| Control Panel Password            | Password   |                    |             |                  | No         | No       | 5                   |
| .htaccess Protection Username     | Text Field |                    |             |                  | No         | No       | 6                   |
| .htaccess Protection Password     | Password   |                    |             |                  | No         | No       | 7                   |
| Public Key Server                 | Text Area  | only if necessary  |             |                  | No         | No       | 8                   |

### Step 5: Adjust the IDs in the customticketpage.php File
On line 29 of the viewticket.tpl file, adjust the custom field IDs to match those in your WHMCS setup. It should look something like this:
```smarty
{if in_array($customfield.id, [3, 4, 5, 6, 7, 8, 9, 10, 11])} <!-- Custom Field IDs -->
```
<strong>How to find your Custom Field IDs:</strong>

1. Create a ticket within the department that has the custom fields (typically called Support).
2. As an admin, open the ticket and go to the Custom Fields section.
3. Right-click on the field and inspect it using your browser's developer tools.
4. You will see a line similar to:

```html
<input type="text" name="customfield[3]" id="customfield3" value="11111111111111111" size="30" class="form-control">
```
Screenshot
![View Admin WHMCS Ticket](https://github.com/jesussuarz/WHMCS-Hook-for-Secure-Credential-Submission-and-Updates/blob/f68162fd21a029d44268cbd463672c15f15903bf/img/view_admin_whmcs_ticket.png)

In this example, the ID of the field is 3. Update the IDs in the array with your specific IDs.

### Adding the "Submit/Update Login Credentials" Button
![View Ticket Button](https://github.com/jesussuarz/WHMCS-Hook-for-Secure-Credential-Submission-and-Updates/blob/f68162fd21a029d44268cbd463672c15f15903bf/img/view_ticket_boton.png)
To add a "Submit/Update Login Credentials" button to your WHMCS template, follow these steps:

1. Open the viewticket.tpl file located in your template directory:
```smarty
/home/your_site/public_html/your_whmcs/templates/twenty-one/viewticket.tpl
```

2. Locate the "Reply" button in the code. It appears as follows:
```smarty
<button id="ticketReply" type="button" class="btn btn-default btn-sm" onclick="smoothScroll('#ticketReplyContainer')">
    <i class="fas fa-pencil-alt fa-fw"></i>
    {lang key='supportticketsreply'}
</button>
```

3. Directly above this button, paste the following HTML code:
```smarty
{if $department == 'Soporte'}           
    <button type="button" class="btn btn-default btn-sm" onclick="window.location.href = window.location.href + '&updatedetails=1';">
        <i class="fas fa-lock-alt fa-fw"></i>
        Submit/Update Login Credentials
    </button>
{/if}
```

This code will add a "Submit/Update Login Credentials" button to your ticket view page. The button will only appear in the "Soporte" (Support) department.

4. Customizing for Other Departments:
* If you want the button to appear in other departments as well, simply modify the condition in the {if $department == 'Soporte'} statement.
* You can use an OR condition or a | pipe to include multiple departments. For example:
```smarty
{if $department == 'Soporte' || $department == 'Sales'}
```
or
```smarty
{if $department == 'Soporte' | $department == 'Sales'}
```
This ensures that the button will only display in the departments specified, giving you control over where it appears in your WHMCS installation.

## Final Result
Once everything is set up correctly, you should see screens similar to this:
![View Ticket User Add](https://github.com/jesussuarz/WHMCS-Hook-for-Secure-Credential-Submission-and-Updates/blob/f68162fd21a029d44268cbd463672c15f15903bf/img/view_ticket_user_add.png)

### How to Use the /clear_custom_fields_cron.php File
To ensure your custom fields are cleared periodically, follow these steps:

1. Upload the File:
* Upload the clear_custom_fields_cron.php file to your WHMCS cron jobs directory.
2. Edit Custom Field IDs:
* Open the clear_custom_fields_cron.php file and locate the line:
```php
$customFieldIDs = [3, 4, 5, 6, 7, 8, 9, 10, 11];
```
Replace the IDs in the array with the custom field IDs specific to your setup. These IDs should have been obtained previously during the setup of your custom fields (as outlined in step 5 of this guide).

3. Set Up the Cron Job:
* On your server, create a cron job to execute the PHP script. The command to use is:
```php
php -q /home/yourweb/public_html/your_whmcs/crons/clear_custom_fields_cron.php
```
* It's recommended to schedule this cron job to run every 24 hours to ensure that all private data in closed tickets is cleared promptly.

4. Testing:
After setting up the cron job, monitor its execution to ensure it’s working correctly. You can manually trigger the cron job and check if the custom fields in closed tickets are being cleared as expected.

I hope this guide helps you implement this powerful hook. If you have any questions, feel free to ask in the comments.

Best regards!
