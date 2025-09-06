// app/Helpers/ResponseHelper.php
namespace App\Helpers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResponseHelper
{
    public static function abortIfEmpty($collection, $message = 'No se encontraron resultados')
    {
        if ($collection->isEmpty()) {
            throw new NotFoundHttpException($message);
        }
    }
}
