<?php

class TemplateManager
{
    /** @const */
    private const SUMMARY_HTML = '[quote:summary_html]';
    /** @const */
    private const SUMMARY = '[quote:summary]';
    /** @const */
    private const DESTINATION_NAME = '[quote:destination_name]';
    /** @const */
    private const DESTINATION_LINK = '[quote:destination_link]';
    /** @const */
    private const FIRST_NAME = '[user:first_name]';
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
        return $this->computeQuotes($text, $data);
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function computeContent($text, array $data)
    {
        $text = $this->computeQuotes($text, $data);
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
            $text = $this->replaceQuotes(self::FIRST_NAME, ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function replaceQuotes($quotes, $replacement, $text)
    {
        return str_replace($quotes, $replacement, $text);
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function computeQuotes($text, array $data)
    {
        $quote = null;
        if (isset($data['quote']) and $data['quote'] instanceof Quote) {
            $quote = $data['quote'];
        }

        /*
         * remplacer les placeholders [quote:*]
         */
        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            $placeholders = [self::SUMMARY_HTML, self::SUMMARY, self::DESTINATION_NAME];
            $informations = [Quote::renderHtml($_quoteFromRepository), Quote::renderText($_quoteFromRepository), $destinationOfQuote->countryName];
            $text = $this->replaceQuotes($placeholders, $informations, $text);
        }

        $destination = '';
        if (isset($destinationOfQuote)) {
            $destination = $usefulObject->url . '/' . $destinationOfQuote->countryName . '/quote/' . $_quoteFromRepository->id;
        }
        $text = $this->replaceQuotes(self::DESTINATION_LINK, $destination, $text);

        return $text;
    }

}
