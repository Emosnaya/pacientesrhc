<?php

namespace Tests\Unit;

use App\Models\Clinica;
use App\Services\CitaAvailabilityService;
use App\Services\PhoneHashService;
use Tests\TestCase;

class AgendaWhatsAppSettingsTest extends TestCase
{
    public function test_estado_inicial_por_clinica(): void
    {
        $service = new CitaAvailabilityService();

        $dental = new Clinica(['cita_estado_inicial' => 'pendiente']);
        $rehab = new Clinica(['cita_estado_inicial' => 'confirmada']);
        $invalido = new Clinica(['cita_estado_inicial' => 'otro']);

        $this->assertSame('pendiente', $service->estadoInicial($dental));
        $this->assertSame('confirmada', $service->estadoInicial($rehab));
        $this->assertSame('confirmada', $service->estadoInicial($invalido));
    }

    public function test_modos_de_solapamiento_y_legacy(): void
    {
        $service = new CitaAvailabilityService();

        $permitir = new Clinica(['citas_solapamiento_modo' => 'permitir']);
        $profesional = new Clinica(['citas_solapamiento_modo' => 'profesional']);
        $clinica = new Clinica(['citas_solapamiento_modo' => 'clinica']);
        $legacy = new Clinica([
            'citas_solapamiento_modo' => null,
            'portal_permite_multiples_citas_mismo_horario' => false,
        ]);

        $this->assertSame(CitaAvailabilityService::MODO_PERMITIR, $service->modoSolapamiento($permitir));
        $this->assertSame(CitaAvailabilityService::MODO_PROFESIONAL, $service->modoSolapamiento($profesional));
        $this->assertSame(CitaAvailabilityService::MODO_CLINICA, $service->modoSolapamiento($clinica));
        $this->assertSame(CitaAvailabilityService::MODO_CLINICA, $service->modoSolapamiento($legacy));
    }

    public function test_phone_hash_normaliza_y_es_estable(): void
    {
        $service = new PhoneHashService();

        $this->assertSame('+525512345678', $service->normalize('55 1234 5678'));
        $this->assertSame('+525512345678', $service->normalize('525512345678'));
        $this->assertNull($service->normalize('123'));

        $hashA = $service->hash('5512345678');
        $hashB = $service->hash('+52 55 1234 5678');
        $this->assertNotNull($hashA);
        $this->assertSame($hashA, $hashB);
        $this->assertSame(64, strlen($hashA));

        $this->assertSame('+525512345678', $service->fromTwilio('whatsapp:+525512345678'));
    }

    public function test_webhook_rechaza_firma_invalida_cuando_twilio_esta_habilitado(): void
    {
        config([
            'services.twilio.enabled' => true,
            'services.twilio.whatsapp_enabled' => true,
            'services.twilio.auth_token' => 'test-token',
        ]);

        $response = $this->post('/api/whatsapp/webhook', [
            'From' => 'whatsapp:+525512345678',
            'Body' => 'CONFIRMAR',
            'MessageSid' => 'SM123',
        ]);

        $response->assertForbidden();
    }

    public function test_webhook_status_tambien_exige_firma_con_twilio_activo(): void
    {
        config([
            'services.twilio.enabled' => true,
            'services.twilio.whatsapp_enabled' => true,
            'services.twilio.auth_token' => 'test-token',
        ]);

        $response = $this->post('/api/whatsapp/status', [
            'MessageSid' => 'SM123',
            'MessageStatus' => 'delivered',
        ]);

        $response->assertForbidden();
    }
}
