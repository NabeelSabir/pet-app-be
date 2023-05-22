<?php


/**
 * Make json object.
 *
 * @param  [integer] code
 * @param  [string] message
 * @param  [boolean] status
 * @param  [string] errors (optional)
 * @param  [object] data
 * @return [string] message
 */
function makeResponse($code, $message, $result=null, $errors=null)
{
    return response()->json([
            'message' => $message,
            'errors' => $errors,
            'result' => $result,
        ], $code);
}