// ========= Custom Editor Script =========

// ====== TOGGLE PICKER PANELS ======
function togglePicker(id, btn) {
  // Hide all panels
  document.querySelectorAll('.picker-panel').forEach(p => {
    p.classList.remove('show');
    p.classList.add('hide');
  });

  // Show target panel
  const panel = document.getElementById(id);
  if (!panel) return;

  // Position under the button
  if (btn) {
    const rect = btn.getBoundingClientRect();
    panel.style.position = 'absolute';
    panel.style.top = `${rect.bottom + window.scrollY + 5}px`;
    panel.style.left = `${rect.left + window.scrollX}px`;
    panel.style.zIndex = '2000';
  }

  panel.classList.remove('hide');
  panel.classList.add('show');
}

// ====== CORE FORMATTER ======
function format(command, value = null) {
  document.execCommand(command, false, value);
  updateCounts();
  autosaveDraft();
}

// ====== FONT FEATURES ======
function applyFontFamily() {
  const font = document.getElementById('fontFamily').value;
  if (font) wrapSelectionWithStyle(`font-family:${font}`);
}

function applyFontSize() {
  const size = document.getElementById('fontSize').value;
  if (size) wrapSelectionWithStyle(`font-size:${size}`);
}

function wrapSelectionWithStyle(style) {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  const span = document.createElement('span');
  span.style.cssText = style;
  range.surroundContents(span);
  updateCounts();
  autosaveDraft();
}

// ====== INSERT ITEMS ======
function insertLink() {
  const url = prompt("Enter the URL:");
  if (url) {
    const sel = window.getSelection();
    const text = sel.toString() || url;
    insertHTMLAtCursor(`<a href="${url}" target="_blank">${text}</a>`);
  }
}

function insertHorizontalLine() {
  insertHTMLAtCursor('<hr>');
}

function insertDateTime() {
  const now = new Date();
  const str = now.toLocaleString();
  insertHTMLAtCursor(`<time datetime="${now.toISOString()}">${str}</time>`);
}

function insertBlockquote() {
  const selText = window.getSelection().toString();
  const quote = selText || prompt("Enter quote:");
  if (quote) insertHTMLAtCursor(`<blockquote>${quote}</blockquote>`);
}

function applyHeading(tag) {
  if (tag) format('formatBlock', tag);
}

function insertCodeBlock() {
  const code = prompt("Enter your code:");
  if (code) {
    const esc = code.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    insertHTMLAtCursor(`<pre><code>${esc}</code></pre>`);
  }
}

function insertTable() {
  const rows = parseInt(prompt("Rows?", "2"), 10);
  const cols = parseInt(prompt("Cols?", "2"), 10);
  if (rows > 0 && cols > 0) {
    let tbl = '<table>';
    for (let r=0; r<rows; r++) {
      tbl += '<tr>' + '<td>&nbsp;</td>'.repeat(cols) + '</tr>';
    }
    tbl += '</table>';
    insertHTMLAtCursor(tbl);
  }
}

// ====== EMOJI & COLOR ======
function insertEmoji(emoji) {
  insertTextAtCursor(emoji);
}

function applyTextColor(color) {
  wrapSelectionWithStyle(`color:${color}`);
}

function applyBgColor(color) {
  wrapSelectionWithStyle(`background-color:${color}`);
}

// ====== MEDIA UPLOAD ======
function uploadMedia(accept, cb) {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = accept;
  input.onchange = () => {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => cb(e.target.result);
    reader.readAsDataURL(file);
  };
  input.click();
}

function uploadImage() {
  uploadMedia('image/*', insertImage);
}

function uploadVideo() {
  uploadMedia('video/*', insertVideo);
}

function insertImage(src) {
  insertMedia(src, 'img');
}

function insertVideo(src) {
  insertMedia(src, 'video', true);
}

function insertMedia(src, tag, controls=false) {
  const el = document.createElement(tag);
  el.src = src;
  if (controls) el.controls = true;
  el.style.width = '50%';
  el.style.resize = 'both';
  el.style.overflow = 'hidden';

  const figure = document.createElement('figure');
  const caption = document.createElement('figcaption');
  caption.contentEditable = true;
  caption.textContent = 'Enter caption...';
  figure.append(el, caption);

  insertElementAtCursor(figure);
}

// ====== INSERT HELPERS ======
function insertHTMLAtCursor(html) {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  range.deleteContents();
  const temp = document.createElement('div');
  temp.innerHTML = html;
  const frag = document.createDocumentFragment();
  while (temp.firstChild) frag.appendChild(temp.firstChild);
  range.insertNode(frag);
  updateCounts();
  autosaveDraft();
}

function insertTextAtCursor(text) {
  document.execCommand('insertText', false, text);
  updateCounts();
  autosaveDraft();
}

function insertElementAtCursor(node) {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  range.deleteContents();
  range.insertNode(node);
  range.setStartAfter(node);
  sel.removeAllRanges();
  sel.addRange(range);
  updateCounts();
  autosaveDraft();
}

// ====== CONTENT MANAGEMENT ======
function clearContent() {
  if (confirm('Clear all content?')) {
    document.getElementById('editor').innerHTML = '';
    updateCounts();
    autosaveDraft();
  }
}

function savePost() {
  const content = document.getElementById('editor').innerHTML;
  console.log('Post content:', content);
  alert('Content ready to save.');
}

// ====== STATS & AUTOSAVE ======
function updateCounts() {
  const text = document.getElementById('editor').innerText.trim();
  const words = text ? text.split(/\s+/).length : 0;
  const chars = text.length;
  document.getElementById('wordCount').textContent = words;
  document.getElementById('charCount').textContent = chars;
}

function autosaveDraft() {
  const content = document.getElementById('editor').innerHTML;
  localStorage.setItem('autosaveDraft', content);
}

// ====== RESTORE ON LOAD ======
window.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('autosaveDraft');
  if (saved) document.getElementById('editor').innerHTML = saved;
  updateCounts();

  // Bind dropdowns
  document.getElementById('fontFamily').onchange = applyFontFamily;
  document.getElementById('fontSize').onchange = applyFontSize;
});

// ====== ALIGNMENT & LINE HEIGHT ======
function getParentBlock(node) {
  while (node && node !== document.body) {
    if (node.nodeType === 1 && window.getComputedStyle(node).display === 'block') {
      return node;
    }
    node = node.parentNode;
  }
  return null;
}

function setAlignment(type) {
  const sel = window.getSelection(); if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  let block = getParentBlock(range.startContainer);
  if (!block || block === document.body) {
    const p = document.createElement('p');
    p.append(range.extractContents());
    range.insertNode(p);
    block = p;
  }
  block.style.textAlign = (type === 'justify' ? 'justify' : type);
}

function setLineHeight(height) {
  const sel = window.getSelection(); if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  let block = getParentBlock(range.startContainer);
  if (!block || block === document.body) {
    const p = document.createElement('p');
    p.append(range.extractContents());
    range.insertNode(p);
    block = p;
  }
  block.style.lineHeight = height;
}

  document.querySelector('a[href="#top-header"]').addEventListener('click', function (e) {
    e.preventDefault();
    const offset = 100; // adjust based on navbar height
    const target = document.querySelector('#top-header');
    const targetPosition = target.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
  });

