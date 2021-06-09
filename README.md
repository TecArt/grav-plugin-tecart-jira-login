[![TecArt GmbH](tecart-logo-rgba_h120.png)](https://www.tecart.de)
# TecArt Jira Login Plugin

The **TecArt Jira Login** Plugin for [Grav CMS](http://github.com/getgrav/grav) to provide a Atlassian Jira basic authentication via cURL on your [Admin plugin](https://github.com/getgrav/grav-plugin-admin).

To send a request using basic authentication, you'll need the following information:
[Basic authentication](https://developer.atlassian.com/server/jira/platform/basic-authentication/).

## Installation

Installing the TecArt Jira Login plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the [Admin plugin](https://github.com/getgrav/grav-plugin-admin).

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install tecart-jira-login

This will install the TecArt Jira Login plugin into your `/user/plugins`-directory within [Grav CMS](http://github.com/getgrav/grav). Its files can be found under `/your/site/grav/user/plugins/tecart-jira-login`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `tecart-jira-login`.

You should now have all the plugin files under

    /your/site/grav/user/plugins/tecart-jira-login

> NOTE: This plugin is a modular component for [Grav CMS](http://github.com/getgrav/grav) and requires other plugins to operate:

    - { name: grav, version: '>=1.7.7' }
    - { name: login, version: '>=3.4.1' }
    - { name: admin, version: '>=1.10.6' }

### Admin Plugin

You can install the plugin directly by browsing the `Plugins` menu and clicking on the `Add` button.

## Configuration

Here is the default configuration and an explanation of available options:

```yaml
enabled: false                                                    - Plugin is disabled by default to allow configuration
save_as_account: false                                            - Allowed users who login to be saved to the /users/accounts directory
jira_url: https://jira.tecart.de                                  - URL to your Jira system
jira_api: rest/api/2/issue/createmeta                             - Jira API path - if changes are required
admin_language: de                                                - Admin Panel language when Jira user logged in
admin_users: frank-panser, soeren-mueller,christiana-holland-jobb - Jira User that should have admin rights in admin panel, add comma separated
```

Note that if you configure this plugin with the [Admin plugin](https://github.com/getgrav/grav-plugin-admin), a file with your configuration named tecart-jira-login.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the Admin.

Note that if you configure this plugin manually, you should copy the `user/plugins/tecart-jira-login/tecart-jira-login.yaml` to `user/config/plugins/tecart-jira-login.yaml` and only edit that copy.

## Credits

This is also an extension of the [Login plugin](https://github.com/getgrav/grav-plugin-login).

Atlassian. Pty Ltd
[Basic authentication](https://developer.atlassian.com/server/jira/platform/basic-authentication/).

## To Do

- N/A

## Known Issues

- N/A
