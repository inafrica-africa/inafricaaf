<?php
// Shared by api/poll.php, api/send-message.php, api/forward-message.php — the
// "turn a batch of tblnetworkmessages rows into the JSON shape the chat UI
// renders" logic lives in exactly one place.

// Fetches messages with id > $sinceId (or all, if $sinceId is 0) visible to
// $myUserId (excludes their own "delete for me" hides), newest-safe
// chronological order, with reply/forward quote previews resolved.
function networkFetchMessages($con, $myUserId, $sinceId = 0, $limit = 200) {
    $stmt = $con->prepare("
        SELECT m.id, m.UserId, m.MessageType, m.MessageText, m.MediaPath,
               m.MediaDurationSeconds, m.ReplyToMessageId, m.ForwardedFromMessageId,
               m.IsDeletedForEveryone, m.CreatedDate,
               u.Name AS SenderName, u.Status AS SenderStatus
        FROM tblnetworkmessages m
        JOIN tblnetworkusers u ON u.id = m.UserId
        WHERE m.Is_Active = 1
          AND m.id > ?
          AND NOT EXISTS (
              SELECT 1 FROM tblnetworkmessagehidden h
              WHERE h.MessageId = m.id AND h.UserId = ?
          )
        ORDER BY m.id ASC
        LIMIT ?
    ");
    $stmt->bind_param("iii", $sinceId, $myUserId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($rows)) {
        return [];
    }

    // Resolve every distinct reply/forward target referenced by this batch
    // in one extra query, rather than one query per row.
    $refIds = [];
    foreach ($rows as $row) {
        if ($row['ReplyToMessageId']) {
            $refIds[(int) $row['ReplyToMessageId']] = true;
        }
        if ($row['ForwardedFromMessageId']) {
            $refIds[(int) $row['ForwardedFromMessageId']] = true;
        }
    }

    $refLookup = [];
    if (!empty($refIds)) {
        $ids = implode(',', array_map('intval', array_keys($refIds)));
        $refResult = mysqli_query($con, "
            SELECT m.id, m.MessageType, m.MessageText, m.IsDeletedForEveryone, u.Name AS SenderName
            FROM tblnetworkmessages m
            JOIN tblnetworkusers u ON u.id = m.UserId
            WHERE m.id IN ($ids)
        ");
        if ($refResult) {
            while ($ref = mysqli_fetch_assoc($refResult)) {
                $refLookup[(int) $ref['id']] = $ref;
            }
        }
    }

    $buildQuote = function ($refId) use ($refLookup) {
        if (!$refId || !isset($refLookup[$refId])) {
            return null;
        }
        $ref = $refLookup[$refId];
        if ($ref['IsDeletedForEveryone']) {
            return ['deleted' => true, 'senderName' => $ref['SenderName']];
        }
        $preview = $ref['MessageType'] === 'text'
            ? mb_substr((string) $ref['MessageText'], 0, 120)
            : ucfirst($ref['MessageType']);
        return ['deleted' => false, 'senderName' => $ref['SenderName'], 'preview' => $preview, 'messageType' => $ref['MessageType']];
    };

    $out = [];
    foreach ($rows as $row) {
        // A deleted-for-everyone message must not hand its original content
        // back to the client at all — the UI hides it either way, but the
        // API response itself shouldn't leak deleted content (e.g. visible
        // in browser devtools) just because the row is still kept around as
        // a tombstone for reply-quote integrity.
        $deleted = (bool) $row['IsDeletedForEveryone'];

        $out[] = [
            'id' => (int) $row['id'],
            'isMine' => (int) $row['UserId'] === (int) $myUserId,
            'senderName' => $row['SenderName'],
            'senderStatus' => $row['SenderStatus'],
            'messageType' => $row['MessageType'],
            'text' => $deleted ? null : $row['MessageText'],
            'mediaPath' => $deleted ? null : $row['MediaPath'],
            'durationSeconds' => (!$deleted && $row['MediaDurationSeconds'] !== null) ? (float) $row['MediaDurationSeconds'] : null,
            'isDeletedForEveryone' => $deleted,
            'isForwarded' => $row['ForwardedFromMessageId'] !== null,
            'replyQuote' => $buildQuote($row['ReplyToMessageId'] ? (int) $row['ReplyToMessageId'] : null),
            'createdDate' => $row['CreatedDate'],
        ];
    }
    return $out;
}

// Same shape, for a single freshly-inserted/forwarded message id.
function networkFetchOneMessage($con, $myUserId, $messageId) {
    $rows = networkFetchMessages($con, $myUserId, $messageId - 1, 1);
    return $rows[0] ?? null;
}
