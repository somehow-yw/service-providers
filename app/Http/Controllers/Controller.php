<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Responses\ResponseFormatTrait;

abstract class Controller extends BaseController
{
    use ResponseFormatTrait;
    use AuthorizesRequests, DispatchesJobs;
    use ValidatesRequests {
        // 重写 构建自己的验证响应
        buildFailedValidationResponse as parentBuildFailedValidationResponse;
        throwValidationException as parentThrowValidationException;
    }

    protected function throwValidationException(Request $request, $validator)
    {
        throw new AppException($this->buildFailedValidationResponse(
            $request, $this->formatValidationErrors($validator)
        ));
    }

    /**
     * Override method defined in ValidatesRequests in order to
     * make an application specific validation error response
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $errors
     *
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        /**
         * fetch first entry of the errors, which is
         * derived from validation, keyed by field name
         * and valued by error messages (which is an array,
         * as a field can relate to multiple error messages)
         * we only need the first error message here
         */

        $message = current(current($errors));

        return $message;
    }
}
