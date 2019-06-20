<?php

class TemplateAnnex
{
    /** @const */
    private const SUMMARY_HTML = '[quote:summary_html]';
    /** @const */
    private const SUMMARY = '[quote:summary]';
    /** @const */
    private const DESTINATION_NAME = '[quote:destination_name]';
    /** @const */
    private const DESTINATION_LINK = '[quote:destination_link]';

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    public function replaceQuotes($quotes, $replacement, $text)
    {
        return str_replace($quotes, $replacement, $text);
    }

    /**
     * @param String $text
     * @param array $data
     * @return string
     */
    public function computeQuotes($text, array $data)
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
