.PHONY: test bdd bdd-dry

test:
	composer test

bdd:
	composer bdd

bdd-dry:
	composer bdd:dry
