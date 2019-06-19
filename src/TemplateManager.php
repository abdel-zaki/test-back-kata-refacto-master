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
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    private function computeText($text, array $data)
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
            $text = str_replace($placeholders, $informations, $text);
        }

        $destination = '';
        if (isset($destinationOfQuote)) {
            $destination = $usefulObject->url . '/' . $destinationOfQuote->countryName . '/quote/' . $_quoteFromRepository->id;
        }
        $text = str_replace(self::DESTINATION_LINK, $destination, $text);

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
            $text = str_replace(self::FIRST_NAME, ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

}
