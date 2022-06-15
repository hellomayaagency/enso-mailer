<?php

namespace Hellomayaagency\Enso\Mailer;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Models\MailEvent;
use Hellomayaagency\Enso\Mailer\Models\MailRecipient;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CampaignLinkBreakdown
{
    protected $campaign;

    protected $parser;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;

        $this->parser = $campaign->getParser();
    }

    public static function get(Campaign $campaign)
    {
        return (new static($campaign))->parseMessages();
    }

    protected function parseMessages()
    {
        $links = new Collection;

        $this->getMessageQuery()->chunk(100, function ($recipients) use ($links) {
            $recipients->each(function ($recipient) use ($links) {
                $this->applyClicks($recipient->messages->first(), $links);
            });
        });

        return $links;
    }

    protected function getMessageQuery()
    {
        return MailRecipient::where('campaign_id', $this->campaign->getKey())
            ->has('messages')
            ->with(['messages' => function ($query) {
                return $query->orderBy('triggered_at', 'DESC')
                    ->orderBy('created_at', 'DESC');
            }]);
    }

    protected function applyClicks(MailEvent $message, Collection $links)
    {
        $clicks = Arr::get($message->getPayload(), 'clicks', []);
        $unique_urls = [];

        if (empty($clicks)) {
            return;
        }

        foreach ($clicks as $click) {
            if (strpos($click['url'], 'mandrillapp.com/track/unsub.php') !== false) {
                continue;
            }

            if (!$links->has($click['url'])) {
                $links->put($click['url'], (object) [
                    'total' => 0,
                    'unique' => 0,
                ]);
            }

            $links->get($click['url'])->total += 1;

            if (!in_array($click['url'], $unique_urls)) {
                $unique_urls[] = $click['url'];
                $links->get($click['url'])->unique += 1;
            }
        }
    }
}
