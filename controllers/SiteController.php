<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\LoginForm;
use app\models\SignupForm;
use app\services\LeadService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\base\UserException;

class SiteController extends Controller
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
                        'actions' => ['error', 'login', 'register'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'summary' => $this->leads->statusSummary(),
            'recent' => $this->leads->recent(),
        ]);
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'auth';
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goHome();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->redirect(['login']);
    }

    public function actionRegister(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'auth';
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post()) && ($user = $model->signup()) !== null) {
            Yii::$app->user->login($user);
            Yii::$app->session->setFlash('success', 'Account created. You are signed in as a manager.');

            return $this->goHome();
        }

        $model->password = '';
        $model->password_repeat = '';

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * HTML errors for the UI, JSON errors for /api.
     * The message of an unexpected exception is replaced so production
     * responses do not include a stack trace or internal details.
     */
    public function actionError(): string|array
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception === null) {
            $exception = new HttpException(404, 'Page not found.');
        }

        $statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
        $name = $exception instanceof HttpException ? $exception->getName() : 'Error';
        $message = $exception instanceof UserException
            ? $exception->getMessage()
            : 'An internal server error occurred.';

        if ($this->isApiRequest()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = $statusCode;

            return [
                'message' => $message,
                'status' => $statusCode,
            ];
        }

        $this->layout = Yii::$app->user->isGuest ? 'auth' : 'main';

        return $this->render('error', [
            'name' => $name,
            'message' => $message,
            'statusCode' => $statusCode,
        ]);
    }

    private function isApiRequest(): bool
    {
        $path = Yii::$app->request->pathInfo;

        return $path === 'api' || str_starts_with($path, 'api/');
    }
}
