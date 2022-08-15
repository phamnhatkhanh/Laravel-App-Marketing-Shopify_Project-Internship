<?php
namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
trait ExceptionTrait
{
	public function apiException($request,$e)
	{
        if ($this->isModel($e)) {
            return $this->ModelResponse($e);
        }

        if ($this->isHttp($e)) {
            return $this->HttpResponse($e);
        }
        // if ($this->isQuery($e)) {
        //     return $this->QueryResponse($e);
        // }

        return parent::render($request, $e);

	}

	protected function isModel($e)
	{
		return $e instanceof ModelNotFoundException;
	}
	protected function isQuery($e)
	{
		return $e instanceof QueryException;
	}

	protected function isHttp($e)
	{
		return $e instanceof NotFoundHttpException;
	}

	protected function ModelResponse($e)
	{
		return response()->json([
                    'errors' => 'Product Model not found'
                ],Response::HTTP_NOT_FOUND);
	}
	protected function QueryResponse($e)
	{
		return response()->json([
                    'status' => false,
                    'errors' => 'Can not access DB'
                ],Response::HTTP_NOT_FOUND);
	}

	protected function HttpResponse($e)
	{
		return response()->json([
                    'errors' => 'Incorect route'
                ],Response::HTTP_NOT_FOUND);
	}
}
