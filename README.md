[![Build Status](https://secure.travis-ci.org/Blackshawk/Are-You-A-Human.png?branch=master)](http://travis-ci.org/Blackshawk/Are-You-A-Human)

PHP 5.3 AYAH
------------
For more information see the [Are You A Human][1] web site. The goal of the product is to provide a more interactive (and infinitely less frustrating) form of captcha for websites. Instead of hideously mangled text you play a small interactive game with very basic instructions.

As a note - I am not affiliated with the AYAH company or their product. I simply ran across it one day, decided it was cool, and decided to modify their PHP library to make it namespaced and easily autoloaded by today's modern frameworks. This project (which can also be found on [Packagist][2]) should easily slot into any framework with a PSR-0 compliant autoloader (ZF, ZF2, Symfony, etc.) although I haven't had a lot of time to test integration with any of these.

I also added some basic unit testing for this project. There are still a few spots I am unsatisfied with, but this is a direct port of the Are You A Human company's library that they ship, so this hardly a ground-up invention on my part.

I'll post a code tutorial soon although it isn't difficult to use and I did document most of the methods fairly well.

If there are problems please fork and submit pull requests, or email me at jarrod@squarecrow.com.


  [1]: http://www.areyouahuman.com/
  [2]: http://packagist.org/packages/Blackshawk/AYAH
