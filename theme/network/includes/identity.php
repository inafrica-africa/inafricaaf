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

// Deliberately NOT Unicode flag emoji: those are two regional-indicator
// codepoints that only render as an actual flag glyph if the OS/browser's
// font has a matching bitmap for that pair — and Windows' default emoji
// font (Segoe UI Emoji) has a long-standing, well-known gap here, showing
// either the raw two-letter code or an empty box instead of a flag. Pure
// CSS gradients on a fixed-size chip render identically everywhere, no
// font/OS dependency at all. Simplified to each flag's main colour bands
// (no emblems/stars/crests — illegible at this size on a real flag too).
const NETWORK_FLAGS = [
    ['Nigeria', 'v', ['#008751', '#ffffff', '#008751']],
    ['Ghana', 'h', ['#ce1126', '#fcd116', '#006b3f']],
    ['Senegal', 'v', ['#00853f', '#fdef42', '#e31b23']],
    ['Mali', 'v', ['#14b53a', '#fcd116', '#ce1126']],
    ['Guinea', 'v', ['#ce1126', '#fcd116', '#009460']],
    ['Cameroon', 'v', ['#007a5e', '#ce1126', '#fcd116']],
    ["Cote d'Ivoire", 'v', ['#f77f00', '#ffffff', '#009e60']],
    ['Ethiopia', 'h', ['#078930', '#fcdd09', '#da121a']],
    ['Rwanda', 'h', ['#00a1de', '#fad201', '#20603d']],
    ['Sierra Leone', 'h', ['#1eb53a', '#ffffff', '#0072c6']],
    ['Egypt', 'h', ['#ce1126', '#ffffff', '#000000']],
    ['Sudan', 'h', ['#d21034', '#ffffff', '#000000']],
    ['Burkina Faso', 'h', ['#ef2b2d', '#009e49']],
    ['Kenya', 'h', ['#000000', '#bb0000', '#006600']],
    ['Uganda', 'h', ['#000000', '#fcdc04', '#d90000']],
    ['Morocco', 'h', ['#c1272d', '#c1272d']],
    ['Algeria', 'v', ['#006233', '#ffffff']],
    ['Tunisia', 'h', ['#e70013', '#e70013']],
    ['Zimbabwe', 'h', ['#006400', '#ffd200', '#d40000', '#000000', '#d40000', '#ffd200', '#006400']],
    ['Tanzania', 'h', ['#1eb53a', '#000000', '#00a3dd']],
];

function networkFlagBanner() {
    $chips = '';
    foreach (NETWORK_FLAGS as [$name, $direction, $colors]) {
        $stops = [];
        $step = 100 / count($colors);
        foreach ($colors as $i => $color) {
            $from = round($i * $step, 2);
            $to = round(($i + 1) * $step, 2);
            $stops[] = "$color {$from}%";
            $stops[] = "$color {$to}%";
        }
        $gradientDir = $direction === 'v' ? 'to right' : 'to bottom';
        $style = 'background: linear-gradient(' . $gradientDir . ', ' . implode(', ', $stops) . ');';
        $chips .= '<span class="network-flag" title="' . htmlspecialchars($name) . '" style="' . $style . '"></span>';
    }
    echo '<div class="network-flag-banner"><div class="network-flag-banner__track">' . $chips . $chips . '</div></div>';
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
