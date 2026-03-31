{{--
  Gmail recorta HTML > ~102KB; evitar data: con PNG grande. Preferir CID (embed) o URL pública.
--}}
@php
    $h = (int) ($height ?? 72);
    $maxW = (int) ($maxWidth ?? 520);
    $style = isset($style) ? (string) $style : '';
    $imgClass = isset($imgClass) ? trim((string) $imgClass) : '';
    $src = null;
    $path = \App\Helpers\EmailHelper::getLynkamedLogoPath();

    if (isset($message) && $message && $path) {
        $src = \App\Helpers\EmailHelper::embedMailImage($message, $path);
    }

    if (empty($src)) {
        $src = \App\Helpers\EmailHelper::getLynkamedLogoPublicUrl();
    }

    if (empty($src) && ! empty($lynkamedLogo)) {
        $src = $lynkamedLogo;
    }

    if (empty($src)) {
        $src = \App\Helpers\EmailHelper::getLynkamedLogoBase64();
    }
@endphp
@if(!empty($src))
<img src="{{ $src }}" alt="Lynkamed"@if($imgClass !== '') class="{{ $imgClass }}"@endif style="{{ $style }}display:block;margin-left:auto;margin-right:auto;background-color:transparent !important;background:transparent !important;border:0;outline:0;max-height:{{ $h }}px;height:auto;width:auto;max-width:{{ $maxW }}px;object-fit:contain;vertical-align:middle;">
@endif
