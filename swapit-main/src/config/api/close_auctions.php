<?php


define('CRON_CONTEXT', true);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$db  = db();
$now = date('Y-m-d H:i:s');

echo "[{$now}] Starting close_auctions cron job\n";


$findExpired = $db->prepare("
    SELECT
        a.id          AS auction_id,
        a.listing_id,
        a.current_bid,
        a.start_price,
        l.user_id     AS seller_id,
        l.title       AS listing_title
    FROM auctions a
    JOIN listings l ON l.id = a.listing_id
    WHERE a.status  = 'live'
      AND a.ends_at <= :now
");
$findExpired->execute([':now' => $now]);
$expiredAuctions = $findExpired->fetchAll(PDO::FETCH_ASSOC);

if (empty($expiredAuctions)) {
    echo "[{$now}] No expired auctions found. Exiting.\n";
    exit(0);
}

echo "[{$now}] Found " . count($expiredAuctions) . " expired auction(s) to process.\n";


foreach ($expiredAuctions as $auction) {
    $auctionId = (int) $auction['auction_id'];
    $listingId = (int) $auction['listing_id'];
    $sellerId  = (int) $auction['seller_id'];
    $title     = $auction['listing_title'];

    echo "[{$now}] Processing auction #{$auctionId} (listing #{$listingId}: \"{$title}\")\n";

    try {
        $db->beginTransaction();

        
        $winnerStmt = $db->prepare("
            SELECT
                b.id      AS bid_id,
                b.user_id AS winner_id,
                b.amount  AS winning_amount,
                u.username,
                u.email
            FROM bids b
            JOIN users u ON u.id = b.user_id
            WHERE b.auction_id = :auction_id
            ORDER BY b.amount DESC
            LIMIT 1
        ");
        $winnerStmt->execute([':auction_id' => $auctionId]);
        $winner = $winnerStmt->fetch(PDO::FETCH_ASSOC);

        
        if ($winner) {
            $db->prepare("
                UPDATE auctions
                SET status      = 'ended',
                    winner_id   = :winner_id,
                    current_bid = :winning_amount
                WHERE id = :auction_id
            ")->execute([
                ':winner_id'      => $winner['winner_id'],
                ':winning_amount' => $winner['winning_amount'],
                ':auction_id'     => $auctionId,
            ]);

            echo "[{$now}]   Winner: user #{$winner['winner_id']} ({$winner['username']}) with bid \${$winner['winning_amount']}\n";
        } else {
            $db->prepare("
                UPDATE auctions
                SET status    = 'ended',
                    winner_id = NULL
                WHERE id = :auction_id
            ")->execute([':auction_id' => $auctionId]);

            echo "[{$now}]   No bids placed. Auction closed with no winner.\n";
        }

        
        $db->prepare("UPDATE listings SET status = 'ended' WHERE id = :listing_id")
            ->execute([':listing_id' => $listingId]);

        
        if ($winner) {
            $winLink = '/listing/' . $listingId;
            insertNotification($db, $winner['winner_id'], 'system',
                'You won the auction!',
                "Congratulations! You won the auction for \"{$title}\" with a bid of \${$winner['winning_amount']}.",
                $winLink
            );
            insertNotification($db, $sellerId, 'system',
                'Your auction ended',
                "Your auction \"{$title}\" has ended. Winner: {$winner['username']} (\${$winner['winning_amount']}).",
                $winLink
            );
        } else {
            insertNotification($db, $sellerId, 'system',
                'Your auction ended with no bids',
                "Your auction \"{$title}\" has ended with no bids. You can re-list it as active.",
                '/listing/' . $listingId
            );
        }

        $db->commit();
        echo "[{$now}]   Auction #{$auctionId} successfully closed.\n";

    } catch (Throwable $e) {
        $db->rollBack();
        echo "[{$now}]   ERROR closing auction #{$auctionId}: " . $e->getMessage() . "\n";
    }
}

$done = date('Y-m-d H:i:s');
echo "[{$done}] close_auctions cron job finished.\n";
exit(0);




function insertNotification(PDO $db, int $userId, string $type, string $title, string $body, string $link): void
{
    try {
        $db->prepare("
            INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at)
            VALUES (:user_id, :type, :title, :body, :link, 0, NOW())
        ")->execute([
            ':user_id' => $userId,
            ':type'    => $type,
            ':title'   => $title,
            ':body'    => $body,
            ':link'    => $link,
        ]);
    } catch (PDOException $e) {
        echo "  [WARN] Could not insert notification: " . $e->getMessage() . "\n";
    }
}
