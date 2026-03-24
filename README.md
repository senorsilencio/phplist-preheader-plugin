# phpList Preheader Plugin

A professional extension for phpList that adds a dedicated **Preheader** (Preview Text) management tab to the campaign editor. This allows editors to define the short summary text shown in email inboxes without modifying the HTML template for every campaign.

## Key Features

* **Dedicated UI Tab:** Adds a "Preheader" tab to the "Send a Message" workflow.
* **Safe Saving:** Intelligent POST handling ensures preheader data is not overwritten when switching between other tabs (Content, Format, etc.).
* **Inbox Optimization:** Automatically injects invisible padding (`&nbsp;&zwnj;`) to prevent "View in Browser" or footer links from leaking into the inbox preview.
* **Late Injection:** Uses high-priority hooks to ensure the preheader is placed correctly even in complex templates.

## Compatibility Table

| Provider / Client | Preheader Displayed? | Max Character Count | Notes |
| :--- | :---: | :--- | :--- |
| **Apple Mail** | ✔️ Yes | ~140 chars | High visibility; settings allow 1-5 lines. |
| **Outlook (Desktop)** | ✔️ Yes | ~255 chars | Very generous; shows under the subject. |
| **gmail.com** | ✔️ Yes | ~97 chars | Shared line with subject; truncated if subject is long. |
| **iOS (iPhone/iPad)** | ✔️ Yes | 81 - 137 chars | iPhone: 81-137 (vert/horiz); iPad: 87 chars. |
| **AOL Mail** | ✔️ Yes | ~75 chars | Standard inbox preview. |
| **t-online.de / yahoo.de** | ✔️ Yes | Variable | Good support in modern web interfaces. |
| **Android / Windows Phone** | ✔️ Yes | ~40 chars | Very limited space; prioritize your CTA! |
| **web.de / gmx.de** | ❌ No* | - | Often pulls first raw body text (e.g., "External Sender" warnings). |
| **freenet.de / outlook.com**| ❌ No* | - | May vary by account type or UI version. |
| **Thunderbird** | ❌ No | - | Focuses on subject/sender; rarely shows preview text. |

*\*Note: Some clients marked with "No" do not have a dedicated preheader field but will simply display the first few words of the email body. In these cases, the "External Sender" warning often displaces the intended preheader.*

## Important Factors

### 1. The "First Text" Rule
Email clients scan the HTML from the top down. If your mail server or gateway injects a security warning (e.g., "CAUTION: External Sender") at the very top of the `<body>`, this warning will likely override your preheader in the inbox view.

### 2. Invisible Padding
This plugin automatically appends a string of non-breaking spaces (`&nbsp;&zwnj;`) to your text. This "fills up" the preview area in generous clients like Outlook, preventing footer links or "Unsubscribe" buttons from leaking into the preview.

### 3. Subject Line Length
In clients like Gmail, the Subject and Preheader share the same line. A very long Subject line will hide your Preheader regardless of its length.

---

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
