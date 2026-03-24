<?php
/**
 * PreheaderPlugin
 *
 * Adds a "Preheader" field to the campaign form.
 * Editors can enter preheader text without editing the HTML.
 *
 * In the template, place the marker [PREHEADER] at the desired location:
 *
 * <span style="display:none;max-height:0;overflow:hidden;">
 * [PREHEADER]
 * </span>
 *
 * The plugin replaces [PREHEADER] with the entered text during sending.
 */
class PreheaderPlugin extends phplistPlugin
{
    public $name        = 'Preheader Plugin';
    public $version     = '1.0.0';
    public $authors     = 'Christian Bieber';
    public $description = 'Allows defining a preheader text per campaign.';
    public $enabled     = 1;

    // Custom DB table for preheader texts
    var $DBstruct = array(
        'preheader' => array(
            'id'        => array('integer not null primary key auto_increment', 'ID'),
            'messageid' => array('integer not null',                            'Campaign ID'),
            'preheader' => array('text',                                        'Preheader text'),
            'unique_1'  => array('unique_messageid (messageid)',                ''),
        ),
    );

    public function dependencyCheck()
    {
        return [
            'phpList 3.3.0 or later' => version_compare(VERSION, '3.3.0') >= 0,
        ];
    }

    // ----------------------------------------------------------------
    // Tab title in the campaign form
    // ----------------------------------------------------------------
    public function sendMessageTabTitle($messageid = 0)
    {
        return 'Preheader';
    }

    // ----------------------------------------------------------------
    // Tab content: Input field for the preheader text
    // ----------------------------------------------------------------
    public function sendMessageTab($messageid = 0, $messagedata = [])
    {
        // Load existing value from DB (when editing a campaign)
        $current = '';
        if ($messageid > 0) {
            $row = Sql_Fetch_Array_Query(
                sprintf(
                    'SELECT preheader FROM %s WHERE messageid = %d',
                    $this->tables['preheader'],
                    (int) $messageid
                )
            );
            if (!empty($row['preheader'])) {
                $current = htmlspecialchars($row['preheader']);
            }
        }

        return '
        <div class="field">
            <label for="preheader_text">
                Preheader text
                <small style="display:block;color:#666;font-weight:normal;">
                    Short preview text that appears next to the subject line in email clients 
                    (recommended: 40–130 characters). Place <code>[PREHEADER]</code> 
                     at the desired position in your template.
                </small>
            </label>
            <input
                type="text"
                id="preheader_text"
                name="preheader_text"
                value="' . $current . '"
                maxlength="200"
                style="width:100%;"
                placeholder="e.g.: Get 20% discount now – only until Sunday!"
            >
            <small id="preheader_counter" style="color:#999;">0 / 130 characters</small>
        </div>
        <script>
        (function() {
            var input   = document.getElementById("preheader_text");
            var counter = document.getElementById("preheader_counter");
            function update() {
                var len = input.value.length;
                counter.textContent = len + " / 130 characters";
                counter.style.color = len > 130 ? "#c00" : "#999";
            }
            input.addEventListener("input", update);
            update();
        })();
        </script>
        ';
    }


    // ----------------------------------------------------------------
    // Save: called when the campaign is saved
    // ----------------------------------------------------------------
    public function sendMessageTabSave($messageid = 0, $data = [])
    {
        if ($messageid <= 0) {
            return;
        }

        if (isset($_POST['preheader_text'])) {
            $preheader = trim($_POST['preheader_text']);

            // UPSERT: replace existing entry or create new one
            Sql_Query(
                sprintf(
                    'REPLACE INTO %s (messageid, preheader) VALUES (%d, "%s")',
                    $this->tables['preheader'],
                    (int) $messageid,
                    sql_escape($preheader)
                )
            );
        }
    }

    // ----------------------------------------------------------------
    // Sending: replace [PREHEADER] in HTML with the saved text
    // ----------------------------------------------------------------
// This hook processes the final mail (Template + Content)
public function parseMessage($messageid, $content, $destination, $userdata = null)
{
    return $this->injectPreheader($messageid, $content);
}

// Safety fallback for HTML
public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata = null)
{
    return $this->injectPreheader($messageid, $content);
}

private function injectPreheader($messageid, $content)
{
    if ($messageid <= 0) return $content;

    // Database query
    $row = Sql_Fetch_Array_Query(sprintf(
        'SELECT preheader FROM %s WHERE messageid = %d',
        $this->tables['preheader'],
        (int) $messageid
    ));

    $text = !empty($row['preheader']) ? trim($row['preheader']) : '';

    if ($text !== '') {
        $cleanText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // The padding ensures the inbox preview is filled
        $replacement = $cleanText . str_repeat('&nbsp;&zwnj; ', 200);
    } else {
        $replacement = '';
    }

    // We use an aggressive replacement throughout the document
    return str_replace('[PREHEADER]', $replacement, $content);
}
}