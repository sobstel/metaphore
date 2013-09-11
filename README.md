metaphore
=========

PHP cache library with semaphore (locks to prevent dogpile effect / clobbering updates / stampending requests). Slam defense.

It's an effort to simplify and focus only on core functionality of LSDCache library (https://github.com/gsmlabs/LSDCache).

Deadlock
--------

Deadlock = locked (lock already acquired by other reqeust), but no stale content to serve
