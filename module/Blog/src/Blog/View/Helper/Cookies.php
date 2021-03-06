<?php

namespace Blog\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Http\Header\SetCookie as Cookie;
use Zend\Http\Request;
use Zend\Http\Response;

class Cookies extends AbstractHelper
{

    /**
     * @var \Zend\Http\Request
     */
    protected $request;

    /**
     * @var \Zend\Http\Response
     */
    protected $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * Stupid Cookie check because we have to.
     *
     * @return string
     */
    public function __invoke()
    {
        // this bit will remove the already set cookies
        if ($this->request->getCookie() && !$this->request->getCookie()->offsetExists('COOKIES')) {
            foreach ($this->request->getCookie() as $name => $value) {
                $this->unsetCookie($name);
            }
        }

        // cookies allowed
        if ($this->request->getQuery('cookies')) {
            // set cookie
            $this->setCookie(true);

            // fetch uri
            $uri = $this->request->getUri();

            // redirect
            $this->response->getHeaders()->addHeaderLine('Location', $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath());
        }
        // render only if user has not yet agreed to the use of cookies
        elseif ((!$this->request->getCookie() || !$this->request->getCookie()->offsetExists('COOKIES')) && !preg_match('/\/cookies\.html$/i', $this->request->getUri()->getPath())) {
            return '<div id="cookies-overlay"></div>' . PHP_EOL
                 . '    <div id="cookies">' . PHP_EOL
                 . '        <h1>Cookies in use</h1>' . PHP_EOL
                 . '        <p>By continuing to use <strong>Jaapsblog.nl</strong> you will be agreeing to the website ' . PHP_EOL
                 . '        <a href="/cookies.html">Use Of Cookies</a> while using the website.</p>' . PHP_EOL
                 . '        <p><a rel="nofollow" href="?cookies=1">Continue</a></p>' . PHP_EOL
                 . '</div>'  . PHP_EOL;
        }
    }

    /**
     * Set Cookie "COOKIE" and add to header.
     *
     * @param $value
     * @return void
     */
    protected function setCookie($value)
    {
        $cookie = new Cookie();

        $cookie->setName('COOKIES');
        $cookie->setValue($value);
        $cookie->setPath('/');
        $cookie->setExpires(time()+60*60*24*365); // 1 year
        $cookie->setDomain(preg_replace('/^[a-z]{0,3}\./iU', '', $this->request->getUri()->getHost()));

        $this->response->getHeaders()->addHeader($cookie);
    }

    /**
     * Unset Cookie and add to header.
     *
     * @param $name
     * @return void
     */
    protected function unsetCookie($name)
    {
        $cookie = new Cookie();

        $cookie->setName($name);
        $cookie->setPath('/');
        $cookie->setExpires(time()-9999);
        $cookie->setDomain(preg_replace('/^[a-z]{0,3}\./iU', '', $this->request->getUri()->getHost()));

        $this->response->getHeaders()->addHeader($cookie);
    }

}