<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/2/23
 * Time: 17:37
 */

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\SignatureVerify\SignatureVerifyException;

use App\Utils\RequestDataEncapsulationUtil;

/**
 * Class RequestSignVerify.
 * 签名验证
 * @package App\Http\Middleware
 */
class RequestSignVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param string                    $signKey 签名密钥
     *
     * @return mixed
     * @throws SignatureVerifyException
     */
    public function handle($request, Closure $next, $signKey)
    {
        /*if ( ! $request->isMethod('post')) {
            throw new SignatureVerifyException(SignatureVerifyException::REQUEST_FAILURE);
        }*/

        if (!$request->has('timestamp')) {
            throw new SignatureVerifyException(SignatureVerifyException::TIMESTAMP_NOT_FAILURE);
        }

        if (!$request->has('signature')) {
            throw new SignatureVerifyException(SignatureVerifyException::SIGNA_NOT_FAILURE);
        }

        // 取得签名验证的KEY
        $signKey = empty($signKey) ? config('signature.wie_chat_sign_key') : $signKey;

        $inputAll = $request->all();

        if (!RequestDataEncapsulationUtil::verifyHttpReceiveDataSign($inputAll, $signKey)) {
            throw new SignatureVerifyException(SignatureVerifyException::SIGNATURE_FAILURE);
        }

        return $next($request);
    }
}
