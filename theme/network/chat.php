<?php
session_start();
include(__DIR__ . '/../config.php');
require_once __DIR__ . '/includes/identity.php';
require_once __DIR__ . '/includes/messages.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$me = networkResolveIdentity($con);
if (!$me) {
    header('Location: /network/');
    exit;
}

// Initial page load renders recent history server-side (works without JS,
// and the page isn't blank while the first poll is still in flight); JS
// takes over from here, polling for anything newer than the last rendered id.
$initialMessages = networkFetchMessages($con, $me['id'], 0, 50);
$lastId = 0;
foreach ($initialMessages as $m) {
    $lastId = max($lastId, $m['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <base href="/">
  <title>Let Africa Connects</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>" rel="stylesheet">
  <link href="network/css/network.css?v=<?= @filemtime(__DIR__ . '/css/network.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include(__DIR__ . '/../header.php'); ?>

  <section class="section network-chat-section">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <strong><?= htmlspecialchars($me['Name']) ?></strong>
          <span class="network-message__status-badge"><?= htmlspecialchars($me['Status']) ?></span>
        </div>
        <div class="text-muted small">Let Africa Connects</div>
      </div>

      <div class="network-chat-shell">
        <!-- The flag theme lives behind the message text itself, not as its
             own banner above the interface — a faint, animated layer under
             the (opaque) message list rather than a separate scrolling
             strip competing for space above the chat. -->
        <?php networkFlagBanner(); ?>

        <!-- Populated by JS from the initialMessages array (below) using the
             exact same renderMessage() as newly polled/sent messages, so
             there's one rendering implementation, not a PHP one that could
             drift from the JS one — and so Reply/Forward/Delete on the
             initial history work identically to messages that arrive later
             (a server-rendered-only version of these would have no click
             handlers attached at all). -->
        <div class="network-chat-messages" id="network-messages"></div>

        <div class="network-chat-composer">
          <div class="network-reply-preview" id="network-reply-preview">
            <button type="button" class="close float-right" id="network-reply-cancel" aria-label="Cancel reply">&times;</button>
            <div id="network-reply-preview-text"></div>
          </div>
          <form id="network-composer-form" class="network-composer-row">
            <input type="file" id="network-media-input" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime" style="display:none;">
            <button type="button" id="network-media-btn" class="network-composer-icon-btn" title="Attach image or video (max 90s)">
              <i class="ti-image"></i>
            </button>
            <div class="network-composer-text-wrap">
              <textarea id="network-text-input" class="form-control" rows="1" placeholder="Type a message..."></textarea>
              <div id="network-media-preview" class="small text-muted"></div>
            </div>
            <button type="submit" class="network-composer-send-btn" title="Send">
              <i class="ti-arrow-right"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include(__DIR__ . '/../footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script>
  (function () {
    var CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;
    var MY_USER_ID = <?= (int) $me['id'] ?>;
    var initialMessages = <?= json_encode($initialMessages) ?>;
    var lastId = <?= (int) $lastId ?>;
    var replyTo = null; // {id, senderName, preview}

    var $messages = document.getElementById('network-messages');
    var $form = document.getElementById('network-composer-form');
    var $textInput = document.getElementById('network-text-input');
    var $mediaInput = document.getElementById('network-media-input');
    var $mediaBtn = document.getElementById('network-media-btn');
    var $mediaPreview = document.getElementById('network-media-preview');
    var $replyPreview = document.getElementById('network-reply-preview');
    var $replyPreviewText = document.getElementById('network-reply-preview-text');
    var $replyCancel = document.getElementById('network-reply-cancel');

    // Auto-grow the composer textarea as you type, like WhatsApp's input —
    // a fixed single-line box was clipping longer messages down to a tiny
    // scrollable sliver instead of the box itself getting taller.
    var TEXTAREA_MAX_HEIGHT = 120;
    function autoGrowTextarea() {
      $textInput.style.height = 'auto';
      $textInput.style.height = Math.min($textInput.scrollHeight, TEXTAREA_MAX_HEIGHT) + 'px';
    }
    $textInput.addEventListener('input', autoGrowTextarea);

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.appendChild(document.createTextNode(str == null ? '' : String(str)));
      return div.innerHTML;
    }

    function scrollToBottom() {
      $messages.scrollTop = $messages.scrollHeight;
    }

    function quoteHtml(quote) {
      if (!quote) {
        return '';
      }
      if (quote.deleted) {
        return '<div class="network-message__quote network-message__quote--deleted">' +
          escapeHtml(quote.senderName) + ': This message was deleted</div>';
      }
      return '<div class="network-message__quote"><strong>' + escapeHtml(quote.senderName) + '</strong>: ' +
        escapeHtml(quote.preview) + '</div>';
    }

    function mediaHtml(msg) {
      if (!msg.mediaPath) {
        return '';
      }
      var src = 'network/media/' + encodeURIComponent(msg.mediaPath);
      if (msg.messageType === 'image') {
        return '<div class="network-message__media"><img src="' + src + '" alt="Shared image"></div>';
      }
      if (msg.messageType === 'video') {
        return '<div class="network-message__media"><video src="' + src + '" controls></video></div>';
      }
      return '';
    }

    function renderMessage(msg) {
      var mine = msg.isMine;
      var wrap = document.createElement('div');
      wrap.className = 'network-message ' + (mine ? 'network-message--mine' : 'network-message--theirs') +
        (msg.isDeletedForEveryone ? ' network-message--deleted' : '');
      wrap.setAttribute('data-message-id', msg.id);

      var bodyHtml = '';
      if (msg.isDeletedForEveryone) {
        bodyHtml = '<em>This message was deleted</em>';
      } else {
        if (msg.isForwarded) {
          bodyHtml += '<div class="network-message__forwarded-label"><i class="ti-share"></i> Forwarded</div>';
        }
        bodyHtml += quoteHtml(msg.replyQuote);
        bodyHtml += mediaHtml(msg);
        if (msg.text) {
          bodyHtml += '<div class="network-message__text">' + escapeHtml(msg.text).replace(/\n/g, '<br>') + '</div>';
        }
      }

      // A small caret in the bubble's corner opens a dropdown (Bootstrap's
      // own dropdown component, already loaded — same data-toggle pattern
      // used elsewhere in the theme) instead of a permanent row of text
      // links under every message.
      var menuHtml = '';
      if (!msg.isDeletedForEveryone) {
        var items = '';
        if (msg.text) {
          // Copy before Reply, matching WhatsApp's own ordering — only
          // shown when there's actual text to copy (the message itself,
          // or a caption on media), same as WhatsApp only offering it then.
          items += '<a class="dropdown-item" data-action="copy">Copy</a>';
        }
        items += '<a class="dropdown-item" data-action="reply">Reply</a>' +
          '<a class="dropdown-item" data-action="forward">Forward</a>' +
          '<a class="dropdown-item" data-action="delete-me">Delete for me</a>';
        if (mine) {
          items += '<a class="dropdown-item" data-action="delete-everyone">Delete for everyone</a>';
        }
        // NOT Bootstrap's data-toggle="dropdown" — its JS toggle requires
        // Popper.js, which isn't loaded anywhere in this theme (bootstrap.min.js
        // literally contains the string "Popper is required"), so that
        // toggle silently no-ops on every click. The .dropdown-menu/
        // .dropdown-item CSS classes are plain CSS with no Popper
        // involvement, so those are kept; show/hide is a small
        // dependency-free handler below (see the single document-level
        // click listener) instead.
        menuHtml =
          '<div class="network-message__menu">' +
            '<button type="button" class="network-message__menu-toggle" aria-haspopup="true" aria-expanded="false" aria-label="Message options">' +
              '<i class="ti-angle-down"></i>' +
            '</button>' +
            '<div class="dropdown-menu dropdown-menu-right">' + items + '</div>' +
          '</div>';
      }

      // meta and the menu toggle are flex siblings in a shared header row,
      // not one absolutely-positioned over the other — the previous
      // absolute-positioned toggle sat directly on top of the sender
      // name/status badge for received messages, both visually colliding
      // and (since the badge was painted after it in DOM order) eating the
      // tap before it ever reached the button.
      var metaHtml = mine ? '<span></span>' : '<div class="network-message__meta"><strong>' + escapeHtml(msg.senderName) + '</strong>' +
        '<span class="network-message__status-badge">' + escapeHtml(msg.senderStatus) + '</span></div>';

      // Read receipt: only meaningful to the sender (same as WhatsApp only
      // showing it on your own messages), and only once at least one other
      // person has actually read it.
      var readHtml = '';
      if (mine && !msg.isDeletedForEveryone && msg.readCount > 0) {
        readHtml = '<div class="network-message__read-status"><i class="ti-eye"></i> Read by ' + msg.readCount + '</div>';
      }

      wrap.innerHTML =
        '<div class="network-message__bubble">' +
          (menuHtml ? '<div class="network-message__header">' + metaHtml + menuHtml + '</div>' : (mine ? '' : metaHtml)) +
          bodyHtml +
          readHtml +
        '</div>';

      wrap.querySelectorAll('[data-action]').forEach(function (el) {
        el.addEventListener('click', function () {
          handleAction(el.getAttribute('data-action'), msg);
        });
      });

      return wrap;
    }

    function appendMessage(msg) {
      $messages.appendChild(renderMessage(msg));
      lastId = Math.max(lastId, msg.id);
      scrollToBottom();
    }

    function showToast(text) {
      var toast = document.createElement('div');
      toast.textContent = text;
      toast.style.cssText = 'position:fixed;left:50%;bottom:90px;transform:translateX(-50%);' +
        'background:rgba(0,0,0,.8);color:#fff;padding:8px 16px;border-radius:20px;font-size:13px;' +
        'z-index:2000;pointer-events:none;';
      document.body.appendChild(toast);
      setTimeout(function () { toast.remove(); }, 1500);
    }

    function handleAction(action, msg) {
      if (action === 'copy') {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(msg.text).then(function () {
            showToast('Copied');
          }).catch(function () {
            window.alert('Could not copy. Long-press the message text to copy it manually.');
          });
        } else {
          window.alert('Copy is not supported in this browser. Long-press the message text to copy it manually.');
        }
      } else if (action === 'reply') {
        replyTo = {
          id: msg.id,
          senderName: msg.senderName,
          preview: msg.text ? msg.text.slice(0, 120) : (msg.messageType.charAt(0).toUpperCase() + msg.messageType.slice(1))
        };
        $replyPreviewText.innerHTML = '<strong>' + escapeHtml(replyTo.senderName) + '</strong>: ' + escapeHtml(replyTo.preview);
        $replyPreview.style.display = 'block';
        $textInput.focus();
      } else if (action === 'forward') {
        postForm('network/api/forward-message', { message_id: msg.id }).then(function (data) {
          if (data.ok) {
            appendMessage(data.message);
          } else {
            window.alert(data.error || 'Could not forward this message.');
          }
        });
      } else if (action === 'delete-me') {
        if (!window.confirm('Delete this message for you? Other people will still see it.')) {
          return;
        }
        postForm('network/api/delete-message', { message_id: msg.id, mode: 'me' }).then(function (data) {
          if (data.ok) {
            var el = $messages.querySelector('[data-message-id="' + msg.id + '"]');
            if (el) {
              el.remove();
            }
          } else {
            window.alert(data.error || 'Could not delete this message.');
          }
        });
      } else if (action === 'delete-everyone') {
        if (!window.confirm('Delete this message for everyone?')) {
          return;
        }
        postForm('network/api/delete-message', { message_id: msg.id, mode: 'everyone' }).then(function (data) {
          if (data.ok) {
            var el = $messages.querySelector('[data-message-id="' + msg.id + '"]');
            if (el) {
              msg.isDeletedForEveryone = true;
              el.replaceWith(renderMessage(msg));
            }
          } else {
            window.alert(data.error || 'Could not delete this message.');
          }
        });
      }
    }

    function postForm(url, fields) {
      var body = new URLSearchParams(Object.assign({ csrftoken: CSRF }, fields));
      return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (res) { return res.json(); });
    }

    function poll() {
      fetch('network/api/poll?since=' + lastId, { credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.ok && data.messages && data.messages.length) {
            data.messages.forEach(appendMessage);
          }
        })
        .catch(function () { /* transient network hiccup — next tick retries */ });
    }
    initialMessages.forEach(appendMessage);
    setInterval(poll, 3000);
    scrollToBottom();

    // Per-message options menu: one delegated listener handles every
    // toggle button (present and future — new messages keep arriving via
    // polling), opening/closing the sibling .dropdown-menu directly rather
    // than through Bootstrap's Popper-dependent dropdown JS.
    document.addEventListener('click', function (e) {
      var toggle = e.target.closest('.network-message__menu-toggle');
      if (toggle) {
        var menu = toggle.parentElement.querySelector('.dropdown-menu');
        var wasOpen = menu.classList.contains('show');
        document.querySelectorAll('.network-message__menu .dropdown-menu.show').forEach(function (m) {
          m.classList.remove('show');
        });
        if (!wasOpen) {
          menu.classList.add('show');
          toggle.setAttribute('aria-expanded', 'true');
        } else {
          toggle.setAttribute('aria-expanded', 'false');
        }
        e.stopPropagation();
        return;
      }
      // Any other click (including on a menu item, which also runs its own
      // data-action handler) closes whatever's open.
      document.querySelectorAll('.network-message__menu .dropdown-menu.show').forEach(function (m) {
        m.classList.remove('show');
        var t = m.parentElement.querySelector('.network-message__menu-toggle');
        if (t) { t.setAttribute('aria-expanded', 'false'); }
      });
    });

    $replyCancel.addEventListener('click', function () {
      replyTo = null;
      $replyPreview.style.display = 'none';
    });

    $mediaBtn.addEventListener('click', function () {
      $mediaInput.click();
    });

    $mediaInput.addEventListener('change', function () {
      var file = $mediaInput.files[0];
      if (!file) {
        $mediaPreview.textContent = '';
        return;
      }
      if (file.type.indexOf('video') === 0) {
        var video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = function () {
          window.URL.revokeObjectURL(video.src);
          if (video.duration > 90) {
            window.alert('Videos must be 90 seconds or shorter.');
            $mediaInput.value = '';
            $mediaPreview.textContent = '';
          } else {
            $mediaPreview.textContent = 'Attached: ' + file.name + ' (' + Math.round(video.duration) + 's)';
          }
        };
        video.src = window.URL.createObjectURL(file);
      } else {
        $mediaPreview.textContent = 'Attached: ' + file.name;
      }
    });

    $form.addEventListener('submit', function (e) {
      e.preventDefault();
      var text = $textInput.value.trim();
      var file = $mediaInput.files[0];

      function send(media) {
        var fields = { text: text };
        if (replyTo) {
          fields.reply_to = replyTo.id;
        }
        if (media) {
          fields.media_type = media.mediaType;
          fields.media_path = media.mediaPath;
          if (media.durationSeconds != null) {
            fields.duration_seconds = media.durationSeconds;
          }
        }
        postForm('network/api/send-message', fields).then(function (data) {
          if (data.ok) {
            appendMessage(data.message);
            $textInput.value = '';
            autoGrowTextarea();
            $mediaInput.value = '';
            $mediaPreview.textContent = '';
            replyTo = null;
            $replyPreview.style.display = 'none';
          } else {
            window.alert(data.error || 'Could not send that message.');
          }
        });
      }

      if (!text && !file) {
        return;
      }

      if (file) {
        var formData = new FormData();
        formData.append('csrftoken', CSRF);
        formData.append('file', file);
        fetch('network/api/upload-media', { method: 'POST', body: formData, credentials: 'same-origin' })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (!data.ok) {
              window.alert(data.error || 'Could not upload that file.');
              return;
            }
            send(data);
          });
      } else {
        send(null);
      }
    });
  })();
  </script>
</body>
</html>
