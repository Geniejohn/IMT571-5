<?php
/**
  * This file is a part of the code used in IMT2571 Assignment 5.
  *
  * @author Rune Hjelsvold
  * @version 2018
  */

require_once('Club.php');
require_once('Skier.php');
require_once('YearlyDistance.php');
require_once('Affiliation.php');

/**
  * The class for accessing skier logs stored in the XML file
  */
class XmlSkierLogs
{
    /**
      * @var DOMDocument The XML document holding the club and skier information.
      */
    protected $doc;

    /**
      * @param string $url Name of the skier logs XML file.
      */
    public function __construct($url)
    {
        $this->doc = new DOMDocument();
        $this->doc->load($url);
    }

    /**
      * The function returns an array of Club objects - one for each
      * club in the XML file passed to the constructor.
      * @return Club[] The array of club objects
      */
    public function getClubs()
    {
        $clubs = array();

        // Using Xpath to get all the clubs:
        $content = $this->xpath->query('SkierLogs/Clubs/Club');

        foreach ($content as $club)
        {
          // Sets the name variable to the nodevalue of the first element within the club with the tagname "Name":
          $name = $club->getElementByTagName("Name")->item(0)->nodeValue;
          // Same for City and County:
          $city = $club->getElementsByTagName("City")->item(0)->nodeValue;
          $county = $club->getElementsByTagName("County")->item(0)->nodeValue;

          // Creates a temporary club with this data for insertion into the clubs-array:
          $tempClub = new Club($club->getAttribute('id'), $name, $city, $county);
          // Inserts this club into the clubs-array:
          array_push($clubs, $tempClub);
        }

        return $clubs;
    }

    /**
      * The function returns an array of Skier objects - one for each
      * Skier in the XML file passed to the constructor. The skier objects
      * contains affiliation histories and logged yearly distances.
      * @return Skier[] The array of skier objects
      */
    public function getSkiers()
    {
        $skiers = array();

        // Temporary variable for calculating totaldistances:
        $distanceCount = 0;
        // Again, using xpath to retrieve list of Skiers and seasons:
        $tempSkiers = $this->xpath->query('SkierLogs/Skiers/Skier');
        $seasons = $this->xpath->query('SkierLogs/Season');

        foreach ($tempSkiers as $season)
        {
          // Finding the skier's attributes by getAttribute and direct nodevalue:
          $userName = $skier->getAttribute('userName');
          $firstName = $skier->getElementsByTagName("FirstName")->item(0)->nodeValue;
          $lastName = $skier->getElementsByTagName("LastName")->item(0)->nodeValue;
          $yearOfBirth = $skier->getElementsByTagName("YearOfBirth")->item(0)->nodeValue;

          // Creates a new skier from this data which we will update with affiliation history and yearlydistances:
          $tempSkier = new Skier($userName, $firstName, $lastName, $YearOfBirth);

          // Goes through every season:
          foreach ($seasons as $season)
          {
            // Goes through every Skiers-list in each season:
            foreach ($season->getElementByTagName("Skiers") as $skierAffiliation)
            {
              // Goes through every skier in each club in each season:
              foreach ($skierAffiliation->getElementsByTagName("Skier") as $affiliatedSkier)
              {
                // If skier we are looking at(set each foreach) is registered under this club for this season:
                if ($userName == $affiliatedSkier->getAttribute('userName'))
                {
                  // The skier has relation to a club(IE not in the Skier list of independent skiers):
                  if ($skierAffiliation->getAttribute('clubId'))
                  {
                    // Creates an affiliation between this club and season:
                    $affiliation = new Affiliation($skierAffiliation->getAttribute('clubId'), $season->getAttribute('fallYear'));
                    // Adds this affiliation to our tempSkier:
                    $tempSkier->addAffiliation($affiliation);
                  }
                  // Goes through each log for this skier:
                  foreach ($affiliatedSkier->getElementsByTagName("Log") as $log)
                  {
                    // For each entry in each log for this skier:
                    foreach ($log->getElementsByTagName("Entry") as $entry)
                    {
                      // Computes the total distance from each distance attribute in each entry for each skier:
                      $dist = $entry->getElementsByTagName("Distance");
                      $distanceCount += $dist->item(0)->nodeValue;
                    }
                  }
                  // Creates and stores the YearlyDistance to the skier:
                  $yearlyDistance = new YearlyDistance($season->getAttribute('fallYear'), $distanceCount);
                  $tempSkier->addYearlyDistance($yearlyDistance);

                  // Resets the distanceCount-variable for the next skier:
                  $distanceCount = 0;
                }
              }
            }
          }

          // Adds this skier, complete with affiliation history and yearlydistances to the skier-list:
          array_push($Skiers, $tempSkier);
        }
        return $skiers;
    }
}

?>