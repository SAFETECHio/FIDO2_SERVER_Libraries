<?php
namespace SAFETECHio\FIDO2\U2F;

use JsonSerializable;


class RegistrationRequest implements JsonSerializable
{
    /** Protocol version */
    protected $version = U2FServer::VERSION;

    /** Registration challenge */
    protected $challenge;

    /** Application id */
    protected $appId;

    /**
     * @param string $challenge
     * @param string $appId
     */
    public function __construct($challenge, $appId)
    {
        $this->challenge = $challenge;
        $this->appId = $appId;
    }

    public function version()
    {
        return $this->version;
    }

    public function challenge()
    {
        return $this->challenge;
    }

    public function appId()
    {
        return $this->appId;
    }

    public function jsonSerialize()
    {
        return [
            'version' => $this->version,
            'challenge' => $this->challenge,
            'appId' => $this->appId,
        ];
    }

}