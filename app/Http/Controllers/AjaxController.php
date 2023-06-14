<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjaxController extends Controller {
    private function deepDecode($array) {
        $decoded = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $decoded[$key] = $this->deepDecode($value);
            } elseif (gettype($value) === 'string') {
                $decoded[$key] = json_decode($value, true);
            } else {
                $decoded[$key] = $value;
            }
        }

        return $decoded;
    }

    /**
     * Regular ajax request without image
     *
     * @param Request $request
     * @return array  response code and batch number
     */
    public function handleRequestRegular(Request $request): array {
        $requests = $request->input('queue');
        $batch = intval($request->input('batch'));
        $resp = [];

        foreach ($requests as $single) {
            $name = 'App\Http\Controllers\\' . $single['name'];
            $meth = $single['controller_method'];
            $params = empty($single['params']) ? [] : $single['params'];

            $code   = 200;
            $data   = null;
            $error  = null;
            $ok     = true;
            try {
                $data = (new $name())->$meth($params);
            } catch (\Exception $e) {
                $error = [
                    'code'      =>  $e->getCode(),
                    'message'   =>  $e->getMessage(),
                ];
            } catch (\Error $e) {
                $message    =   $e->getMessage();
                $code       =   $e->getCode();

                if (strpos($message, 'undefined method') !== false) {
                    $error = [
                        'code'      =>   100,
                        'message'   =>   'Metoda ne postoji',
                    ];
                } elseif (strpos($message, 'function toArray()') !== false) {
                    $error = [
                        'code'      =>  101,
                        'mesage'    =>  'Ne možete pozivati toArray metodu na nizu',
                    ];
                } else {
                    $error = [
                        'code'      =>  $code,
                        'message'   =>  $message,
                    ];
                }
            } finally {
                $code   = $error !== null ? 400     :   200;
                $ok     = $error !== null ? false   :   true;
                $resp[] = [
                    'code'      => $code,
                    'ok'        => $ok,
                    'error'     => $error,
                    'data'      => $data,
                    'key'       => $single['name'] . '--' . $meth,
                ];
            }
        }

        return [
            'batch' => $batch,
            'data'  => $resp,
        ];
    }

    /**
     * Raw request with image
     *
     * @param Request $request
     * @return array  response code and batch number
     */
    public function handleRequestRaw(Request $request): array {
        $batch = intval($request->input('batch'));
        $params = $request->all();
        $name = 'App\Http\Controllers\\' . $params['controller_name'];
        $meth = $params['controller_method'];

        unset($params['controller_name']);
        unset($params['controller_method']);
        $code   = 200;
        $data   = null;
        $error  = null;
        $ok     = true;
        try {
            $data = (new $name())->$meth($params);
        } catch (\Exception $e) {
            $error = [
                'code'      =>  $e->getCode(),
                'message'   =>  $e->getMessage(),
            ];
        } catch (\Error $e) {
            $message    =   $e->getMessage();
            $code       =   $e->getCode();

            if (strpos($message, 'undefined method') !== false) {
                $error = [
                    'code'      =>   100,
                    'message'   =>   'Metoda ne postoji',
                ];
            } elseif (strpos($message, 'function toArray()') !== false) {
                $error = [
                    'code'      =>  101,
                    'mesage'    =>  'Ne možete pozivati toArray metodu na nizu',
                ];
            } else {
                $error = [
                    'code'      =>  $code,
                    'message'   =>  $message,
                ];
            }
        } finally {
            $code   = $error !== null ? 400     :   200;
            $ok     = $error !== null ? false   :   true;
            $resp[] = [
                'code'      => $code,
                'ok'        => $ok,
                'error'     => $error,
                'data'      => $data,
                'key'       => $name . '--' . $meth,
            ];
        }
        return [
            'batch' => $batch,
            'data'  => $resp,
        ];
    }
}
