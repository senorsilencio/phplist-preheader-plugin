# phpList Preheader Plugin

A professional extension for phpList that adds a dedicated **Preheader** (Preview Text) management tab to the campaign editor. This allows editors to define the short summary text shown in email inboxes without modifying the HTML template for every campaign.

## Key Features

* **Dedicated UI Tab:** Adds a "Preheader" tab to the "Send a Message" workflow.
* **Safe Saving:** Intelligent POST handling ensures preheader data is not overwritten when switching between other tabs (Content, Format, etc.).
* **Inbox Optimization:** Automatically injects invisible padding (`&nbsp;&zwnj;`) to prevent "View in Browser" or footer links from leaking into the inbox preview.
* **Late Injection:** Uses high-priority hooks to ensure the preheader is placed correctly even in complex templates.

## Installation

1.  Download the `PreheaderPlugin.php` file and the `PreheaderPlugin` directory.
2.  Upload them to your phpList `plugins` folder.
3.  Go to **System > Manage Plugins** in your phpList dashboard and click **Enable**.
4.  The plugin will automatically create the required database table `phplist_preheader`.

## How to Use

### 1. Prepare your Template
Add the `[PREHEADER]` placeholder at the very beginning of your HTML template's `<body>` tag. For best results, use the following "invisible" wrapper:

```html
<body ...>
  <div style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; max-height:0; max-width:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px;">
    [PREHEADER]
  </div>
  ... rest of your template ...


## License
This project is licensed under the AGPL-3.0 License - see the [LICENSE](LICENSE) file for details.
