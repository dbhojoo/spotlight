#!/usr/bin/python
# -*- coding: utf-8 -*-

#just changed the inputs to make it relevant to events and users
# A dictionary of event attendees and their ratings for preferred event type
#all ratings are out of 5 -- this can be used as the main questionnaire for set-up

# for file pick-up and run
from sys import argv

#csv reader
import csv

# declaration of file and input file
script, input_file = argv

def dict(a):
	people = {}
	with a.open(input_file, 'r') as data_file:
		data = csv.DictReader(data_file, delimiter=",")
		for row in data:
			item = people.get(row["user_id"], dict())
			item[row["event_type"]] = int(row["rating"])

			people[row["user_id"]] = item


def print_all(b):
    print b.read()
	
dict(input_file)


