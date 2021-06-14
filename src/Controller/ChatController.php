<?php

namespace App\Controller;

use App\Entity\Caretaker;
use App\Repository\CaretakerRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/chat")
 */
class ChatController extends AbstractController
{
    const API_HOST = 'http://localhost';
    const API_PORT = '3000';
    const CHANEL_ID = '#general';
    const CHAT_AUTH_DATA_NAME = 'chatAuthData';
    private $chatApiErrorKind = null;

    private SessionInterface $session;
    private string $adminAuthToken;
    private string $adminId;
    private string $apiHost;
    private int $apiPort;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }

    public function getApiHost(): string
    {

        $host = $this->apiHost;
        if ($this->apiPort != 80) {
            $host = $host . ':' . $this->apiPort;
        }
        return $host;
    }


    private function initApiConfig(): bool
    {
        $this->chatApiErrorKind = null;
        $cfg = null;
        try {
            $cfg = $this->getParameter('chat_api');
        } catch (EnvNotFoundException $e) {
            $this->chatApiErrorKind = 'no-env';
        } catch (InvalidArgumentException $e) {
            $this->chatApiErrorKind = 'no-cfg';
        }
//        $cfg = $this->getParameter('chat_api');
        if (is_null($cfg) || !is_array($cfg)) {
            return false;
        }

        if (array_key_exists('admin_id', $cfg) && !is_null($cfg['admin_id'])
            && array_key_exists('admin_token', $cfg) && !is_null($cfg['admin_token'])
            && array_key_exists('host', $cfg) && !is_null($cfg['host'])
            && array_key_exists('port', $cfg) && !is_null($cfg['port'])
        ) {
            $this->adminAuthToken = $cfg['admin_token'];
            $this->adminId = $cfg['admin_id'];
            $this->apiHost = $cfg['host'];
            $this->apiPort = $cfg['port'];
            return true;
        }
        return false;
    }

    /**
     * @Route("/", name="chat_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function index(): Response
    {
        if (!$this->initApiConfig()) return $this->redirectToRoute('chat_config_error_spec', ['kind' => $this->chatApiErrorKind]);
        if ($this->pingApiServer() == 408) return $this->redirectToRoute('chat_noresponse');

        $tokenData = $this->getToken();
        if (!is_array($tokenData)) {
            /** @var Caretaker $logged */
            $logged = $this->getUser();
            return $this->redirectToRoute('chat_caretaker', ['slug' => $logged->getSlug()]);
        }

        return $this->render('chat/index.html.twig', [
            'chatUserName' => $tokenData['u']
        ]);
    }

    /**
     * @Route("/config-error", name="chat_config_error", methods={"GET"})
     */
    public function configError(): Response
    {
        return $this->render('chat/config-error.html.twig');
    }

    /**
     * @Route("/config-error/{kind}", name="chat_config_error_spec", methods={"GET"})
     */
    public function configErrorSpecified(string $kind): Response
    {
        return $this->render('chat/config-error.html.twig', [
            'errorKind' => $kind
        ]);
    }

    /**
     * @Route("/logout", name="chat_logout", methods={"GET"})
     */
    public function logout(CaretakerRepository $caretakerRepository): Response
    {
        $this->deleteAuthSession();
        return $this->redirectToRoute('chat_select');
    }

    private function pingApiServer()
    {
        if (!$this->initApiConfig()) return $this->json(['status' => 400, 'data' => ['error' => 'No API config']], 400);

        $client = new CurlHttpClient();
        $res = $client->request('GET', $this->getApiHost() . '/api/info', ['timeout' => 5]);

        $code = 0;
        try {
            $code = $res->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            $code = 408;
        }
        return $code;
    }

    /**
     * @Route("/select", name="chat_select", methods={"GET"})
     */
    public function select(CaretakerRepository $caretakerRepository): Response
    {
        $this->deleteAuthSession();
        return $this->render('chat/select.html.twig', [
            'caretakers' => $caretakerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/fetch-all", name="chat_fetch_all", methods={"GET"})
     */
    public function fetchAll(): Response
    {
        if (!$this->initApiConfig()) return $this->json(['status' => 400, 'data' => ['error' => 'No API config']], 400);
        $tokenData = $this->getToken();
        if (!is_array($tokenData)) return $this->json(['status' => 400, 'data' => ['error' => 'No access token, please select user to chat']], 400);


        $client = new CurlHttpClient();
        $url = $this->getApiHost() . '/api/v1/channels.messages?sort={"ts":1}&roomId=GENERAL';
        if (isset($_GET['updated-at'])) {
            $updatedAt = $_GET['updated-at'];
            $url .= '&query={"_updatedAt":{"$gt":{"$date":"' . $updatedAt . '"}}}';
        } else {
            $url .= '&query={"_updatedAt":{"$gt":{"$date":"1970-01-01T12:00:00"}}}';
        }

        $res = $client->request('GET', $url,
            [
                'headers' => [
                    'X-Auth-Token' => $tokenData['t'],
                    'X-User-Id'    => $tokenData['id'],
                    'Content-type' => 'application/json',
                ],
            ]
        );

        if ($res->getStatusCode() != 200) {
            return $this->json([
                'status' => $res->getStatusCode(),
                'url'    => $url,
                'data'   => $res->getStatusCode()
            ], $res->getStatusCode());
        }
        $content = $res->getContent();
        return $this->json([
            'status' => 200,
            'url'    => $url,
            'data'   => $content
        ]);
    }

    /**
     * @Route("/delete-all", name="chat_delete_all", methods={"GET"})
     * @deprecated This method is used only for dev purposes
     */
    public function deleteAll(): Response
    {

        if (1 == 1) {
            return new Response('Method temporary not allowed ', 405);
        }
        $tokenData = $this->getToken();
        if (!is_array($tokenData)) {
            return $this->json(['status' => 400, 'data' => ['error' => 'No access token, please select user to chat']], 400);
        }
        $client = new CurlHttpClient();
        $url = $this->getApiHost() . '/api/v1/channels.messages?sort={"ts":1}&roomId=GENERAL&count=1000';


        $resGet = $client->request('GET', $url,
            [
                'headers' => [
                    'X-Auth-Token' => $tokenData['t'],
                    'X-User-Id'    => $tokenData['id'],
                    'Content-type' => 'application/json',
                ],
            ]
        );

        if ($resGet->getStatusCode() != 200) {
            return $this->json([
                'status' => $resGet->getStatusCode(),
                'url'    => $url,
                'data'   => $resGet->getStatusCode()
            ], $resGet->getStatusCode());
        }
        $content = $resGet->getContent();
        $deco = json_decode($content, true);

        foreach ($deco['messages'] as $message) {
            $client->reset();
            $resDelete = $client->request('POST', $this->getApiHost() . '/api/v1/chat.delete',
                [
                    'headers' => [
                        'X-Auth-Token' => $this->adminAuthToken,
                        'X-User-Id'    => $this->adminId,
                        'Content-type' => 'application/json',
                    ],
                    'body'    => json_encode([
                        'roomId' => 'GENERAL',
                        'msgId'  => $message['_id'],
                    ])
                ]
            );
//            var_dump($resDelete->getInfo());
        }

        return new Response('foo');

    }


    private function getToken()
    {
        $sesData = $this->session->get(self::CHAT_AUTH_DATA_NAME);
        return (is_null($sesData))
            ? false
            : $sesData;
    }

    /**
     * @Route("/send", name="chat_send", methods={"GET","POST"})
     */
    public function send(Request $request): Response
    {
        if (!$this->initApiConfig()) return $this->json(['status' => 400, 'data' => ['error' => 'No API config']], 400);
        $tokenData = $this->getToken();
        if (!is_array($tokenData)) {
            return $this->json(['status' => 400, 'data' => ['error' => 'No access token, please select user to chat']], 400);
        }


        $client = new CurlHttpClient();
        $client->request('POST', 'http://localhost:3000/api/v1/chat.postMessage',
            [
                'headers' => [
                    'X-Auth-Token' => $tokenData['t'],
                    'X-User-Id'    => $tokenData['id'],
                    'Content-type' => 'application/json',
                ],
                'body'    => json_encode([
                    'channel' => self::CHANEL_ID,
                    'text'    => $request->get('message')
                ])
            ]
        );
        return $this->json(['status', 'ok']);
    }


    /**
     * @Route("/no-response", name="chat_noresponse", methods={"GET"})
     */
    public function noResponse(): Response
    {
        return $this->render('chat/no-response.html.twig');
    }

    /**
     * @Route("/for/caretaker/{slug}", name="chat_caretaker", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function chatCaretaker(string $slug, CaretakerRepository $caretakerRepository): Response
    {

        if (!$this->initApiConfig()) return $this->redirectToRoute('chat_config_error_spec', ['kind' => $this->chatApiErrorKind]);
        if ($this->pingApiServer() == 408) return $this->redirectToRoute('chat_noresponse');

        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);


        if (is_null($caretaker)) {
            return $this->redirectToRoute('chat_select');
        } else {
            /** @var Caretaker $logged */
            $logged = $this->getUser();
            if ($slug != $logged->getSlug()) {
                $caretaker = $caretakerRepository->findOneBy(['slug' => $logged->getSlug()]);
            }
            $userName = 'caretaker_' . $caretaker->getId();

            // Check if user exists

            $client = new CurlHttpClient();
            $url = $this->getApiHost() . '/api/v1/users.list?query={ "username": "' . $userName . '" }';
            $res = $client->request('GET', $url,
                [
                    'headers' => [
                        'X-Auth-Token' => $this->adminAuthToken,
                        'X-User-Id'    => $this->adminId,
                        'Content-type' => 'application/json',
                    ],
                ]
            );

            $code = $res->getStatusCode();
            if ($code == 401) {
                return $this->redirectToRoute('chat_config_error_spec', ['kind' => 'no-auth']);
            }

            $data = json_decode($res->getContent(), true);
            $userId = null;
            if ($data['count'] == 0) {
                $client->reset();
                $res2 = $client->request('POST', $this->getApiHost() . '/api/v1/users.create',
                    [
                        'headers' => [
                            'X-Auth-Token' => $this->adminAuthToken,
                            'X-User-Id'    => $this->adminId,
                            'Content-type' => 'application/json',
                        ],
                        'body'    => json_encode([
                            'email'    => "{$userName}@zoo.fake",
                            'name'     => $caretaker->getName(),
                            'username' => $userName,
                            'password' => self::generateRandomPass(),
                        ])
                    ]
                );

                $content2 = json_decode($res2->getContent(), true);
                $userId = $content2['user']['_id'];


            } else {
                $user = $data['users'][0];
                $userId = $user['_id'];
            }

            $client->reset();
            $resToken = $client->request('POST', $this->getApiHost() . '/api/v1/users.createToken',
                [
                    'headers' => [
                        'X-Auth-Token' => $this->adminAuthToken,
                        'X-User-Id'    => $this->adminId,
                        'Content-type' => 'application/json',
                    ],
                    'body'    => json_encode([
                        'userId' => $userId,
                    ])
                ]);


            if ($resToken->getStatusCode() == 200) {
                $tokenData = json_decode($resToken->getContent(), true);

                $this->setAuthSession($tokenData['data']['authToken'], $tokenData['data']['userId'], $caretaker->getName());
            }


            return $this->redirectToRoute('chat_index');
        }
    }

    private function setAuthSession($token, $id, $userName)
    {
        $this->session->set(self::CHAT_AUTH_DATA_NAME, [
            't'  => $token,
            'id' => $id,
            'u'  => $userName
        ]);
    }

    private function deleteAuthSession()
    {
        $this->session->set(self::CHAT_AUTH_DATA_NAME, null);
    }

    private static function generateRandomPass($len = 32): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
