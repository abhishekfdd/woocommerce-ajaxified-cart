#!/usr/bin/env node
// Generate POT file using wp-pot library programmatically.
const fs = require('fs');
const path = require('path');
const wpPot = require('wp-pot');

const root = process.cwd();
const dest = path.join(root, 'languages', 'abwc-ajax-cart.pot');
try {
  wpPot({
    package: 'Ajaxified Cart for Online Stores',
    domain: 'abwc-ajax-cart',
    src: '**/*.php',
    writeToFile: dest,
    commentKeyword: 'translators:'
  });
  if (fs.existsSync(dest)) {
    let content = fs.readFileSync(dest, 'utf8');
    // Rebuild header block after msgstr "".
    const headerStart = content.indexOf('msgid ""');
    if (headerStart !== -1) {
      const msgstrIndex = content.indexOf('msgstr ""', headerStart);
      if (msgstrIndex !== -1) {
        const afterMsgstr = content.indexOf('\n', msgstrIndex) + 1;
        // Find end of header (first blank line following msgstr lines).
        const headerEndMatch = content.slice(afterMsgstr).match(/^(?:".*"\n)*\n/m);
        let headerEndPos = afterMsgstr;
        if (headerEndMatch) {
          headerEndPos = afterMsgstr + headerEndMatch.index;
        }
        const year = new Date().getFullYear();
		  const iso = new Date().toISOString().slice(0, 19).replace('T', ' ') + '+00:00';
        const headerLines = [
          `"Project-Id-Version: Ajaxified Cart for Online Stores\\n"`,
          `"Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/ajaxified-cart-woocommerce\\n"`,
          `"POT-Creation-Date: ${iso}\\n"`,
          `"MIME-Version: 1.0\\n"`,
          `"Content-Type: text/plain; charset=UTF-8\\n"`,
          `"Content-Transfer-Encoding: 8bit\\n"`,
          `"X-Generator: wp-pot (programmatic)\\n"`,
          `"X-Domain: abwc-ajax-cart\\n"`,
          `"Last-Translator: Abhishek Kumar <abhishekfdd@gmail.com>\\n"`,
          `"Language-Team: Abhishek Kumar <abhishekfdd@gmail.com>\\n"`,
          `"Plural-Forms: nplurals=2; plural=(n != 1);\\n"`
        ].join('\n') + '\n\n';
        content = content.slice(0, afterMsgstr) + headerLines + content.slice(headerEndPos);
        // Update copyright line.
        content = content.replace(/Copyright \(C\) \d{4} [^\n]*/,'Copyright (C) '+year+' Abhishek Kumar');
        // Remove any duplicate legacy header quoted lines between our header and first reference '#:'.
        const afterHeaderPos = content.indexOf(headerLines, afterMsgstr) + headerLines.length;
        const firstRefPos = content.indexOf('#:', afterHeaderPos);
        if (firstRefPos !== -1) {
          const interim = content.slice(afterHeaderPos, firstRefPos);
          // Strip lines starting with a double quote (old header remnants).
          const cleanedInterim = interim
            .split('\n')
            .filter(line => !/^"/.test(line) )
            .join('\n');
          content = content.slice(0, afterHeaderPos) + cleanedInterim + content.slice(firstRefPos);
        }
      }
    }
    fs.writeFileSync(dest, content, 'utf8');
  }
  let count = 0;
  if (fs.existsSync(dest)) {
    const updated = fs.readFileSync(dest, 'utf8');
    count = (updated.match(/^msgid\s+".*"$/gm) || []).length;
  }
  console.log(`Generated POT (${count} msgid entries) at ${dest}`);
  process.exit(0);
} catch (e) {
  console.error('Failed generating POT:', e);
  process.exit(1);
}
