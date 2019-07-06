<?php

namespace SAFETECHio\FIDO2\WebAuthn\Protocol\Assertion;


use SAFETECHio\FIDO2\Exceptions\WebAuthnException;
use SAFETECHio\FIDO2\PublicKeys\PublicKey;
use SAFETECHio\FIDO2\Tools\Tools;
use SAFETECHio\FIDO2\WebAuthn\Protocol\Client\CeremonyType;
use SAFETECHio\FIDO2\WebAuthn\Protocol\COSE\PublicKeyEllipticCurve;
use SAFETECHio\FIDO2\WebAuthn\Protocol\COSE\PublicKeyFactory;
use SAFETECHio\FIDO2\WebAuthn\Protocol\Credentials\ParsedPublicKeyCredential;

class ParsedCredentialAssertionData extends ParsedPublicKeyCredential
{
    /** @var ParsedAssertionResponse $Response */
    public $Response;

    /** @var CredentialAssertionResponse $Raw */
    public $Raw;

    /**
     * ParsedCredentialAssertionData constructor.
     * @param CredentialAssertionResponse $credentialAssertionResponse
     * @throws \SAFETECHio\FIDO2\Exceptions\WebAuthnException
     */
    public function __construct(CredentialAssertionResponse $credentialAssertionResponse)
    {
        $this->Raw = $credentialAssertionResponse;
        $this->ID = $credentialAssertionResponse->ID;
        $this->RawID = Tools::base64u_decode($credentialAssertionResponse->RawID);
        $this->Type = $credentialAssertionResponse->Type;
        $this->Response = new ParsedAssertionResponse($credentialAssertionResponse->AssertionResponse);
    }

    /**
     * @see https://www.w3.org/TR/webauthn/#verifying-assertion
     *
     * @param string $challenge
     * @param bool $verifyUser
     * @param string $relyingPartyID
     * @param string $relyingPartyOrigin
     * @param string $credentialPublicKey
     * @throws WebAuthnException | \ReflectionException
     */
    public function Verify(string $challenge, bool $verifyUser, string $relyingPartyID, string $relyingPartyOrigin, string $credentialPublicKey)
    {
        // Verify the client data against the stored relying party data
        $this->Response->CollectedClientData->Verify($challenge, CeremonyType::GET, $relyingPartyOrigin);

        // SHA256 hash the relying party id
        $RPIDHash = Tools::SHA256($relyingPartyID, true);

        // Verify the authenticator data object
        $this->Response->AuthenticatorData->Verify($verifyUser, $RPIDHash);

        /**
         * @see https://www.w3.org/TR/webauthn/#verifying-assertion
         * 16 - Using the credential public key looked up in step 3, verify that sig is a valid signature over the binary concatenation of authData and hash.
         * Note: This verification step is compatible with signatures generated by FIDO U2F authenticators.
         * @See §6.1.2 FIDO U2F Signature Format Compatibility : https://www.w3.org/TR/webauthn/#sctn-fido-u2f-sig-format-compat
         *
         * WebAuthn\Protocol\Attestation\FormatHandlers\FidoU2FAttestation.php
         */

        /** Hash client data JSON (15 - Let hash be the result of computing a hash over the cData using SHA-256)*/
        $clientDataHash = Tools::SHA256(Tools::base64u_decode($this->Raw->AssertionResponse->ClientDataJSON), true);

        /** From 16 : a valid signature over the binary concatenation of authData and hash */
        $signedData = $this->Raw->AssertionResponse->AuthenticatorData . $clientDataHash;

        /** @var PublicKeyEllipticCurve $publicKey */
        $publicKey = PublicKeyFactory::Make($credentialPublicKey);
        $publicKeyForPEM= "\x04" . $publicKey->XCoord . $publicKey->YCoord;
        $PEM = PublicKey::fromString($publicKeyForPEM)->toPEM();

        $result = openssl_verify($signedData, $this->Response->Signature, $PEM, OPENSSL_ALGO_SHA256);
        if($result !== 1){
            throw new WebAuthnException(
                "Signature does not verify",
                WebAuthnException::ATTESTATION_SIGNATURE_INVALID
            );
        }
    }
}