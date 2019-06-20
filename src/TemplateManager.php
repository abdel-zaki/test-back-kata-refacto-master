<?php

require_once __DIR__ . '/../src/TemplateAnnex.php';

class TemplateManager
{
    /** @const */
    private const FIRST_NAME = '[user:first_name]';
    /** @var */
    private $tempAnnex;

    /**
     * TemplateManager constructor.
     */
    public function __construct()
    {
        $this->tempAnnex = new TemplateAnnex();
    }

    /**
     * @param Template $tpl
     * @param array $data
     * @return Template
     */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeSubject($replaced->subject, $data);
        $replaced->content = $this->computeContent($replaced->content, $data);

        return $replaced;
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function computeSubject($text, array $data)
    {
        return $this->tempAnnex->computeQuotes($text, $data);
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function computeContent($text, array $data)
    {
        $text = $this->tempAnnex->computeQuotes($text, $data);
        /*
         * USER
         * remplacer le placeholder [user:first_name]
         */
        $_user = null;
        if (isset($data['user']) and ($data['user'] instanceof User)) {
            $_user = $data['user'];
        }
        else {
            $_user = ApplicationContext::getInstance()->getCurrentUser();
        }
        if ($_user) {
            $text = $this->tempAnnex->replaceQuotes(self::FIRST_NAME, ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

}
