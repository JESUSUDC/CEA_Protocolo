<?php
namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Controller;

use App\Application\Cellphone\Port\In\DeleteCellphoneUseCase;
use App\Application\Cellphone\Port\In\GetCellphoneByIdUseCase;
use App\Application\Cellphone\Port\In\ListCellphonesUseCase;
use App\Application\Cellphone\Port\In\RegisterCellphoneUseCase;
use App\Application\Cellphone\Port\In\UpdateCellphoneUseCase;
use App\Application\Cellphone\Service\ListCellphoneService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Entrypoint\Rest\Cellphones\Mapper\CellphoneHttpMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class CellController extends Controller
{
    public function __construct(
        protected ListCellphonesUseCase $listUseCase,
    ) {}
    
    public function index(): Response
    {
        //$mensaje = $this->listUseCase->Hola("Mundo");
        return response("Hola Mundo", 200);
    }
}