<?php
// Shared by every Networking page/endpoint. Resolves who's asking from the
// persistent device cookie — this module has no login/logout, ever; a
// device is either recognized (an existing tblnetworkdevices row) or it
// isn't, and if it isn't, the caller decides what to do (show registration,
// point at restore-access, etc.) rather than this file redirecting anyone.
//
// Only meaningful once config.php ($con) is already loaded.

const NETWORK_DEVICE_COOKIE = 'inafrica_network_device';
const NETWORK_DEVICE_COOKIE_TTL = 63072000; // 2 years

// A representative spread of African flag emoji for the animated banner —
// not all 54 (the strip would just take longer to visibly repeat; these
// render identically via Unicode regional-indicator pairs, no image assets).
const NETWORK_FLAG_EMOJIS = '🇰🇪 🇳🇬 🇬🇭 🇿🇦 🇪🇬 🇸🇳 🇷🇼 🇪🇹 🇹🇿 🇺🇬 🇲🇦 🇩🇿 🇨🇮 🇨🇲 🇿🇲 🇿🇼 🇧🇼 🇳🇦 🇲🇿 🇹🇳 🇲🇱 🇧🇫 🇸🇱 🇱🇷 🇸🇩';

function networkFlagBanner() {
    $flags = htmlspecialchars(NETWORK_FLAG_EMOJIS);
    echo '<div class="network-flag-banner"><div class="network-flag-banner__track">'
        . $flags . '&nbsp;&nbsp;&nbsp;' . $flags . '</div></div>';
}

// Returns the tblnetworkusers row (with id, Name, WhatsApp, Email, Status,
// CountryId) for the current device, or null if there's no cookie or it
// doesn't match an active user. Also bumps LastSeenDate on both the device
// and user rows so the admin engagement metric reflects real activity.
function networkResolveIdentity($con) {
    $token = $_COOKIE[NETWORK_DEVICE_COOKIE] ?? '';
    if ($token === '') {
        return null;
    }

    $stmt = $con->prepare("
        SELECT u.id, u.Name, u.WhatsApp, u.Email, u.Status, u.CountryId
        FROM tblnetworkdevices d
        JOIN tblnetworkusers u ON u.id = d.UserId
        WHERE d.DeviceToken = ? AND u.Is_Active = 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null;
    }

    $touch = $con->prepare("UPDATE tblnetworkdevices SET LastSeenDate = NOW() WHERE DeviceToken = ?");
    $touch->bind_param("s", $token);
    $touch->execute();
    $touch->close();

    $touchUser = $con->prepare("UPDATE tblnetworkusers SET LastSeenDate = NOW() WHERE id = ?");
    $touchUser->bind_param("i", $user['id']);
    $touchUser->execute();
    $touchUser->close();

    return $user;
}

// Issues a fresh device token for $userId, sets the cookie, and returns the
// token. Used both by fresh registration and by restore-access — in both
// cases this is an ADD, never touching any other device row for that user.
function networkIssueDevice($con, $userId) {
    $token = bin2hex(random_bytes(32));

    $stmt = $con->prepare("INSERT INTO tblnetworkdevices (UserId, DeviceToken) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $token);
    $stmt->execute();
    $stmt->close();

    setcookie(NETWORK_DEVICE_COOKIE, $token, [
        'expires' => time() + NETWORK_DEVICE_COOKIE_TTL,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);

    return $token;
}
