<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\LeadStatus;
use app\models\Lead;
use app\models\LeadSearch;
use app\models\User;
use app\services\LeadService;
use app\services\RbacService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
        return [
            'access' => [
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
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new LeadSearch();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(
                Yii::$app->request->queryParams,
                (int) Yii::$app->params['leadPageSize'],
            ),
            'managers' => $this->leads->managerOptions(),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $actor = $this->currentUser();
        $model = new Lead([
            'scenario' => $this->leads->scenarioFor($actor),
            'manager_id' => $actor->getId(),
            'status' => LeadStatus::New->value,
        ]);

        if ($model->load(Yii::$app->request->post())) {
            $this->leads->assignOwner($model, $actor);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Lead created.');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'managers' => $this->leads->managerOptions(),
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $actor = $this->currentUser();
        $model = $this->findModel($id);
        $model->scenario = $this->leads->scenarioFor($actor);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Lead updated.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'managers' => $this->leads->managerOptions(),
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $this->leads->delete($this->findModel($id), $this->currentUser());
        Yii::$app->session->setFlash('success', 'Lead deleted.');

        return $this->redirect(['index']);
    }

    private function findModel(int $id): Lead
    {
        $model = Lead::find()->with(['manager'])->andWhere(['id' => $id])->one();
        if (!$model instanceof Lead) {
            throw new NotFoundHttpException('Lead not found.');
        }

        return $model;
    }

    private function currentUser(): User
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User) {
            throw new ForbiddenHttpException('Authentication required.');
        }

        return $identity;
    }
}
