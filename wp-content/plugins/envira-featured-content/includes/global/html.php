<?php
/**
 * Envira_Featured_Content_Truncate_HTML class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// @codingStandardsIgnoreFile
// Third party class.

/**
 * Envira_Featured_Content_Truncate_HTML class.
 *
 * @since 1.0.0
 *
 * @package Envira_Featured_Content
 * @author  Envira Team
 */
class Envira_Featured_Content_Truncate_HTML {

	public static function truncate_words( $html, $limit, $ellipsis = '...' ) {

		if ( $limit <= 0 || $limit >= self::countWords( strip_tags( $html ) ) ) {
			return $html;
		}

		$html = apply_filters('envira_featured_content_truncate_HTML', $html, $limit, $ellipsis );

		$dom = new DOMDocument();
		$dom->loadHTML( $html );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		$it = new DOMWordsIterator( $body );

		foreach ( $it as $word ) {
			if ( $it->key() >= $limit ) {
				$currentWordPosition = $it->currentWordPosition();
				$curNode             = $currentWordPosition[0];
				$offset              = $currentWordPosition[1];
				$words               = $currentWordPosition[2];

				$curNode->nodeValue = substr( $curNode->nodeValue, 0, $words[ $offset ][1] + strlen( $words[ $offset ][0] ) );

				self::removeProceedingNodes( $curNode, $body );
				self::insertEllipsis( $curNode, $ellipsis );
				break;
			}
		}

		return preg_replace( '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML() );
	}

	private static function removeProceedingNodes( DOMNode $domNode, DOMNode $topNode ) {
		$nextNode = $domNode->nextSibling;

		if ( $nextNode !== null ) {
			self::removeProceedingNodes( $nextNode, $topNode );
			$domNode->parentNode->removeChild( $nextNode );
		} else {
			// scan upwards till we find a sibling
			$curNode = $domNode->parentNode;
			while ( $curNode !== $topNode ) {
				if ( $curNode->nextSibling !== null ) {
					$curNode = $curNode->nextSibling;
					self::removeProceedingNodes( $curNode, $topNode );
					$curNode->parentNode->removeChild( $curNode );
					break;
				}
				$curNode = $curNode->parentNode;
			}
		}
	}

	private static function insertEllipsis( DOMNode $domNode, $ellipsis ) {
		$avoid = array( 'a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5' ); // html tags to avoid appending the ellipsis to

		if ( in_array( $domNode->parentNode->nodeName, $avoid ) && $domNode->parentNode->parentNode !== null ) {
			// Append as text node to parent instead
			$textNode = new DOMText( $ellipsis );

			if ( $domNode->parentNode->parentNode->nextSibling ) {
				$domNode->parentNode->parentNode->insertBefore( $textNode, $domNode->parentNode->parentNode->nextSibling );
			} else {
				$domNode->parentNode->parentNode->appendChild( $textNode );
			}
		} else {
			// Append to current node
			$domNode->nodeValue = rtrim( $domNode->nodeValue ) . $ellipsis;
		}
	}

	private static function countWords( $text ) {
		$words = preg_split( "/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY );
		return count( $words );
	}

}

/**
 * Iterates individual words of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMWordsIterator($doc) as $word) echo $word;
 *
 * @author pjgalbraith http://www.pjgalbraith.com
 * @author porneL http://pornel.net (based on DOMLettersIterator available at http://pornel.net/source/domlettersiterator.php)
 * @license Public Domain
 */
if ( ! class_exists( 'DOMWordsIterator' ) ) :

	final class DOMWordsIterator implements Iterator {

		private $start, $current;
		private $offset, $key, $words;

		/**
		 * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
		 */
		function __construct( DOMNode $el ) {
			if ( $el instanceof DOMDocument ) {
				$this->start = $el->documentElement;
			} elseif ( $el instanceof DOMElement ) {
				$this->start = $el;
			} else {
				throw new InvalidArgumentException( 'Invalid arguments, expected DOMElement or DOMDocument' );
			}
		}

		/**
		 * Returns position in text as DOMText node and character offset.
		 * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
		 * node may be NULL if iterator has finished.
		 *
		 * @return array
		 */
		function currentWordPosition() {
			return array( $this->current, $this->offset, $this->words );
		}

		/**
		 * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
		 *
		 * @return DOMElement
		 */
		function currentElement() {
			return $this->current ? $this->current->parentNode : null;
		}

		// Implementation of Iterator interface
		function key() {
			return $this->key;
		}

		function next() {
			if ( ! $this->current ) {
				return;
			}

			if ( $this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE ) {
				if ( $this->offset == -1 ) {
					$this->words = preg_split( "/[\n\r\t ]+/", $this->current->textContent, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE );
				}
				$this->offset++;

				if ( $this->offset < count( $this->words ) ) {
					$this->key++;
					return;
				}
				$this->offset = -1;
			}

			while ( $this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild ) {
				$this->current = $this->current->firstChild;
				if ( $this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE ) {
					return $this->next();
				}
			}

			while ( ! $this->current->nextSibling && $this->current->parentNode ) {
				$this->current = $this->current->parentNode;
				if ( $this->current === $this->start ) {
					$this->current = null;
					return;}
			}

			$this->current = $this->current->nextSibling;

			return $this->next();
		}

		function current() {
			if ( $this->current ) {
				return $this->words[ $this->offset ][0];
			}
			return null;
		}

		function valid() {
			return ! ! $this->current;
		}

		function rewind() {
			$this->offset  = -1;
			$this->words   = array();
			$this->current = $this->start;
			$this->next();
		}
	}

endif;
