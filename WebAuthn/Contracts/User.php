<?php

namespace SAFETECHio\FIDO2\WebAuthn\Contracts;

use SAFETECHio\FIDO2\WebAuthn\Credential;

interface User {

    /**
     * Relying Party's User ID
     * @return string
     */
    public function WebAuthnID(): string;

    /**
     * Relying party's User Name
     * @return string
     */
    public function WebAuthnName(): string;

    /**
     * User's Display Name
     * @return string
     */
    public function WebAuthnDisplayName(): string;

    /**
     * User's icon URL
     * @return string
     */
    public function WebAuthnIcon(): string;

    /**
     * User's stored Credentials
     * @return Credential[]
     */
    public function WebAuthnCredentials(): array;

    /**
     * User's stored Credentials
     * @return Credential[]
     */
    public function WebAuthnAllowedCredentials(): array;
}