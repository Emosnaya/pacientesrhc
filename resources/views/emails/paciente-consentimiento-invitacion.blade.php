<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <div style="text-align: center; margin-bottom: 32px;">
        @include('emails.partials.lynkamed-logo-inline', ['height' => 72])
    </div>
    <p>Hola {{ $nombrePaciente ?: 'Paciente' }},</p>

    @if(($contexto ?? 'registro') === 'nueva_vinculacion')
        <p>
            <strong>{{ $clinicaNombre }}</strong> ha solicitado vincular tu expediente clínico a su espacio en el sistema.
        </p>
        <p>
            Para continuar de forma adecuada bajo la <strong>Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)</strong>,
            necesitamos que confirmes que has leído y aceptas el <strong>aviso de privacidad</strong> y los <strong>términos y condiciones</strong> aplicables,
            y que autorizas que este establecimiento pueda acceder y utilizar la información de tu expediente cuando corresponda a tu atención.
        </p>
        <p style="background: #f1f5f9; border-radius: 8px; padding: 14px 16px; font-size: 14px;">
            Esto puede incluir <strong>datos de identificación y contacto</strong>, <strong>historia clínica</strong>, <strong>estudios e informes</strong>,
            <strong>resultados de laboratorio</strong>, <strong>recetas</strong>, <strong>notas de consulta</strong> y, en su caso, información que otros consultorios o clínicas
            vinculados a tu persona hayan compartido en el expediente, siempre conforme a la normatividad y a las finalidades informadas en el aviso de privacidad.
        </p>
    @else
        <p>
            <strong>{{ $clinicaNombre }}</strong> ha registrado tus datos en su sistema.
            Para continuar conforme a la <strong>Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)</strong>,
            necesitamos que confirmes que has leído y aceptas el <strong>aviso de privacidad</strong> y los <strong>términos y condiciones</strong> aplicables.
        </p>
        <p style="font-size: 14px; color: #475569;">
            Tu expediente puede llegar a incluir estudios, laboratorios, recetas y demás información clínica que el personal autorizado registre conforme a la ley.
        </p>
    @endif

    @if(!empty($urlAviso))
        <p><a href="{{ $urlAviso }}">Ver aviso de privacidad</a></p>
    @endif
    @if(!empty($urlTerminos))
        <p><a href="{{ $urlTerminos }}">Ver términos y condiciones</a></p>
    @endif
    <p style="margin: 28px 0;">
        <a href="{{ $urlAceptacion }}" style="display: inline-block; background: #071F4A; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600;">
            Revisar y aceptar
        </a>
    </p>
    <p style="font-size: 13px; color: #64748b;">
        Este enlace es personal y caduca en {{ $diasValidez }} días. Si no esperabas este mensaje, ignóralo o contacta al establecimiento.
    </p>
    <p style="font-size: 12px; color: #94a3b8;">No respondas a este correo automático.</p>
</body>
</html>
