<?php

declare(strict_types=1);

namespace app\controllers\api;

use app\models\Lead;
use app\models\LeadSearch;
use app\models\User;
use app\services\LeadService;
use app\services\RbacService;
use Yii;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Session-authenticated JSON API for leads.
 *
 * CSRF is off because this is not a browser form. The session cookie is
 * SameSite=Lax, so a foreign site cannot attach it to a cross-site POST.
 * Guests get 401. A manager who calls delete gets 403.
 */
class LeadController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly LeadService $leads,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator'], $behaviors['rateLimiter']);

        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_JSON,
            'text/html' => Response::FORMAT_JSON,
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update'],
                    'roles' => [RbacService::PERMISSION_MANAGE_LEADS],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [RbacService::PERMISSION_DELETE_LEADS],
                ],
            ],
            'denyCallback' => static function (): void {
                if (Yii::$app->user->isGuest) {
                    throw new UnauthorizedHttpException('Authentication required.');
                }

                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            },
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actionIndex(): array
    {
        $sort = Yii::$app->request->get('sort', '');
        if (is_string($sort) && !LeadSearch::isSortValid($sort)) {
            throw new BadRequestHttpException('Unknown sort attribute.');
        }

        $search = new LeadSearch();
        $provider = $search->search(Yii::$app->request->queryParams, 20);
        if ($search->hasErrors()) {
            return $this->validationFailed($search);
        }

        $pagination = $provider->getPagination();
        if ($pagination === false) {
            throw new BadRequestHttpException('Pagination is not available.');
        }

        return [
            'items' => $provider->getModels(),
            'pagination' => [
                'total' => (int) $provider->getTotalCount(),
                'page' => $pagination->getPage() + 1,
                'per_page' => $pagination->getPageSize(),
                'page_count' => $pagination->getPageCount(),
            ],
        ];
    }

    public function actionView(int $id): Lead
    {
        return $this->findLead($id);
    }

    public function actionCreate(): Lead|array
    {
        $actor = $this->currentUser();
        $lead = new Lead(['scenario' => $this->leads->scenarioFor($actor)]);
        $lead->load($this->body(), '');
        $this->leads->assignOwner($lead, $actor);

        if (!$lead->save()) {
            return $this->validationFailed($lead);
        }

        Yii::$app->response->setStatusCode(201);
        Yii::$app->response->headers->set(
            'Location',
            Yii::$app->urlManager->createAbsoluteUrl(['/api/lead/view', 'id' => $lead->id]),
        );

        return $this->findLead((int) $lead->id);
    }

    public function actionUpdate(int $id): Lead|array
    {
        $actor = $this->currentUser();
        $lead = $this->findLead($id);
        $lead->scenario = $this->leads->scenarioFor($actor);
        $lead->load($this->body(), '');

        if (!$lead->save()) {
            return $this->validationFailed($lead);
        }

        return $this->findLead($id);
    }

    public function actionDelete(int $id): void
    {
        $this->leads->delete($this->findLead($id), $this->currentUser());
        Yii::$app->response->statusCode = 204;
    }

    private function findLead(int $id): Lead
    {
        $lead = Lead::find()->with(['manager'])->andWhere(['id' => $id])->one();
        if (!$lead instanceof Lead) {
            throw new NotFoundHttpException('Lead not found.');
        }

        return $lead;
    }

    private function currentUser(): User
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        return $identity;
    }

    /**
     * @return array<string, mixed>
     */
    private function body(): array
    {
        $payload = Yii::$app->request->bodyParams;
        if (!is_array($payload) || $payload === [] || array_is_list($payload)) {
            throw new BadRequestHttpException('Request body must be a JSON object.');
        }

        return $payload;
    }

    /**
     * @return array{message: string, status: int, errors: array<string, list<string>>}
     */
    private function validationFailed(\yii\base\Model $model): array
    {
        Yii::$app->response->statusCode = 422;

        /** @var array<string, list<string>> $errors */
        $errors = $model->getErrors();

        return [
            'message' => 'Validation failed.',
            'status' => 422,
            'errors' => $errors,
        ];
    }
}
