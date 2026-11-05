'use strict';
// Focused contracts for Eclipse mobile navigation accessibility.
// Run: node quality/mobile-navigation-test.cjs
const fs = require('node:fs');
const assert = require('node:assert/strict');
const root = require('node:path').resolve(__dirname, '..');
const script = fs.readFileSync(root + '/eclipse/js/theme.js', 'utf8');
const header = fs.readFileSync(root + '/eclipse/header.thtml', 'utf8');
const assertIncludes = (value, message) => assert.ok(script.includes(value), message);

assert.match(header, /class="menu-toggle"[^>]*aria-expanded="false"[^>]*aria-controls="eclipse-menu-panel"/);
assert.match(header, /class="menu-toggle"[^>]*aria-expanded="false"[^>]*aria-controls="navigation_ul"/);
assertIncludes('function closeMobileMenu(restoreFocus)', 'Missing shared mobile close handler');
assertIncludes("event.key !== 'Escape'", 'Escape keyboard handling missing');
assertIncludes("closeMobileMenu(true)", 'Escape must restore focus to menu toggle');
assertIncludes("menu.classList.remove('is-open')", 'Menu close must remove open class');
assertIncludes("item.classList.remove('is-submenu-open')", 'Closing menu must reset open submenus');
assertIncludes("link.setAttribute('aria-expanded', 'false')", 'Submenu expanded state not reset');
assertIncludes("if (restoreFocus) toggle.focus()", 'Menu focus restoration missing');
assertIncludes("closeMobileMenu(false)", 'Link selection must close the mobile menu');
console.log('Eclipse mobile navigation accessibility checks passed');
