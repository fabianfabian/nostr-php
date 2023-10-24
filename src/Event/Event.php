<?php

namespace swentel\nostr\Event;

use Mdanter\Ecc\Crypto\Signature\SchnorrSignature;
use swentel\nostr\EventInterface;

class Event implements EventInterface
{

    /**
     * The event kind.
     *
     * Override this property in your custom events to set the value
     * immediately.
     *
     * @var int
     */
    protected int $kind = 0;

    /**
     * The event id.
     *
     * @var string
     */
    protected string $id = '';

    /**
     * The event signature.
     *
     * @var string
     */
    protected string $sig = '';

    /**
     * The public key.
     *
     * @var string
     */
    protected string $pubkey;

    /**
     * The event content.
     *
     * @var string
     */
    protected string $content = '';

    /**
     * The created at timestamp.
     *
     * @var int
     */
    protected int $created_at = 0;

    /**
     * The event tags.
     *
     * @var array
     */
    protected array $tags = [];

    /**
     * Base constructor for events.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->setKind($this->kind);
    }

    /**
     * Returns true if $json encodes a valid Nostr event.
     */
    public static function verify(string $json): bool
    {
        $event = json_decode($json, false, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);

        if (!$event) { return false; }

        if (!$event instanceof \stdClass
            || !property_exists($event, 'id')
            || !property_exists($event, 'pubkey')
            || !property_exists($event, 'created_at')
            || !property_exists($event, 'kind')
            || !property_exists($event, 'tags')
            || !property_exists($event, 'content')
            || !property_exists($event, 'sig')
            || !is_string($event->id)
            || !is_string($event->pubkey)
            || !is_int($event->created_at)
            || !is_int($event->kind)
            || !is_array($event->tags)
            || !is_string($event->content)
            || !is_string($event->sig)
        ) {
            return false;
        }

        

        foreach ($event->tags as $tag) {
            if (!is_array($tag)) {
                return false;
            }

            foreach ($tag as $value) {
                if (!is_string($value)) {
                    return false;
                }
            }
        }

        $computedId = hash('sha256', json_encode(
            [0, $event->pubkey, $event->created_at, $event->kind, $event->tags, $event->content],
            \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
        ));

        if (!hash_equals($computedId, $event->id)) {
            return false;
        }

        return (new SchnorrSignature())->verify($event->pubkey, $event->sig, $event->id);
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicKey(string $public_key)
    {
        $this->pubkey = $public_key;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicKey(): string
    {
        return $this->pubkey;
    }

    /**
     * {@inheritdoc}
     */
    public function setSignature(string $sig)
    {
        $this->sig = $sig;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSignature(): string
    {
        return $this->sig;
    }

    /**
     * {@inheritdoc}
     */
    public function setKind(int $kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKind(): int
    {
       return $this->kind;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(string $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag($key, $value)
    {
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(int $time)
    {
        $this->created_at = $time;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): int
    {
        return $this->created_at;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $ignore_properties = []): array
    {
        $array = [];
        foreach (get_object_vars($this) as $key => $val) {
            if (in_array($key, $ignore_properties))
            {
                continue;
            }
            $array[$key] = $val;
        }
        return $array;
    }

}
