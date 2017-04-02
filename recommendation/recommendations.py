#!/usr/bin/python
# -*- coding: utf-8 -*-
# this is based on the OReilly working example
#just changed the inputs to make it relevant to events and users
# A dictionary of event attendees and their ratings for preferred event type
#all ratings are out of 5 -- this can be used as the main questionnaire for set-up

people = {

	'Lisa Rose':
		{'Heavy Metal': 2.5, 'Hip Hop': 3.5, 'Dance': 3.0, 'Trance': 3.5, 'Pop': 2.5, 'Jazz': 3.0}
	,'Gene Seymour':
		{'Heavy Metal': 1.0, 'Hip Hop': 5.0, 'Dance': 2.0, 'Trance': 1.5, 'Pop': 3.5, 'Jazz': 3.5}
	,'Michael Phillips':
		{'Heavy Metal': 5.0, 'Hip Hop': 0.0, 'Dance': 0.0, 'Trance': 3.5, 'Pop': 0.0, 'Jazz': 3.0}
	,'Claudia Puig':
		{'Heavy Metal': 1.5, 'Hip Hop': 1.5, 'Trance': 1.5, 'Pop': 1.5, 'Jazz': 1.0}
	,'Mick LaSalle':
		{'Heavy Metal': 2.0, 'Hip Hop': 3.0, 'Dance': 2.0, 'Trance': 2.5, 'Pop': 1.5, 'Jazz': 2.0}
	,'Jack Matthews':
		{'Heavy Metal': 1.5, 'Hip Hop': 4.5, 'Dance': 4.0, 'Trance': 3.5, 'Pop': 4.5, 'Jazz': 0.0}
	,'Toby':
		{'Heavy Metal': 0.0, 'Hip Hop': 4.5, 'Dance': 4.0, 'Trance': 1.5, 'Pop': 2.5, 'Jazz': 4.0}
	,'Mark Filge':
		{'Heavy Metal': 2.0, 'Hip Hop': 0.0, 'Dance': 0.0, 'Trance': 0.5, 'Pop': 0.0, 'Jazz': 0.0}
	,'Gene Seymour':
		{'Heavy Metal': 0.0, 'Hip Hop': 3.0, 'Dance': 0.0, 'Trance': 0.5, 'Pop': 2.5, 'Jazz': 4.5}
	,'Michael Johnson':
		{'Heavy Metal': 3.0, 'Hip Hop': 0.0, 'Dance': 0.0, 'Trance': 3.5, 'Pop': 0.0, 'Jazz': 3.0}
	,'Sam Jones':
		{'Heavy Metal': 1.5, 'Hip Hop': 1.5, 'Dance': 1.0, 'Trance': 1.5, 'Pop': 1.5, 'Jazz': 1.0}
	,'Demis Bhojoo':
		{'Hip Hop': 4.5, 'Dance': 2.0, 'Trance': 1.0, 'Pop': 2.5, 'Jazz': 4.0}
	,'Jack Smith':
		{'Heavy Metal': 3.5, 'Dance': 4.0, 'Trance': 3.5, 'Pop': 4.5, 'Jazz': 0.0}
	,'Fuud Smithwen':
		{'Heavy Metal': 0.0, 'Hip Hop': 4.5, 'Dance': 4.0, 'Trance': 1.5, 'Pop': 2.5, 'Jazz': 4.0}
	
	}


from math import sqrt

# returns a distance-based similarity score for person1 and person2

def sim_distance(prefs, person1, person2):
	#get the list of shared_items 
	si = {}
	for item in prefs[person1]:
		if item in prefs[person2]:
			si[item] = 1
			
	#if they have no ratings in common, return 0
	
	if len(si) == 0: return 0
	
	#add up the squares of all the differences
	sum_of_squares = sum([pow(prefs[person1][item]-prefs[person2][item],2) for item in si])
	
	return 1 / (1 + sqrt(sum_of_squares))
	
#returns the Pearson coef value for p1 and p2
def sim_pearson(prefs,p1,p2):
	#get the list of mutually rated items
	si={}
	for item in prefs[p1]:
		if item in prefs[p2]: 
			si[item]=1
		
	#find the number of elements
	n = len(si)
	
	#if they have no ratings in common, then return 0
	if n == 0: return 0
	
	#adding all the preferences
	sum1 = sum([prefs[p1][it] for it in si])
	sum2 = sum([prefs[p2][it] for it in si])
	
	#sum up the squares
	sum1Sq = sum([pow(prefs[p1][it],2) for it in si])
	sum2Sq = sum([pow(prefs[p2][it],2) for it in si])

	#sum up the products
	pSum = sum([prefs[p1][it]*prefs[p2][it] for it in si])
	
	#calculate the pearson coef
	num = pSum - (sum1 * sum2 / n)
	
	den = sqrt(sum1Sq - pow(sum1,2)/n) * sqrt(sum2Sq - pow(sum2,2)/n)
	
	if den == 0: return 0
	
	r = num / den
	
	return r
	
#returns the best match for the person from the perfs dictionary
#number of results and similarity functional are optional parameters

def topMatches(prefs,person,n = 5, similarity=sim_pearson):
	scores = [(similarity(prefs,person,other),other)
		for other in prefs if other != person]
		
	#sorting in descending order to get best first
	scores.sort()
	scores.reverse()
	return scores[0:n]
	
#gets recommendatons for a person using weighted average
#of every other user

def getRec(prefs,person,similarity=sim_pearson):
	totals = {}
	simSums = {}
	for other in prefs:
		#eliminate yourself to not compare
		if other == person: continue
		sim = similarity(prefs,person,other)
		
		#ignore scores of zero or lower
		if sim <= 0: continue
		for item in prefs[other]:
		
			#only score unseen categories
			if item not in prefs[person]or prefs[person][item] == 0:
				#similarity * score
				totals.setdefault(item,0)
				
				totals[item] += prefs[other][item] * sim
				
				#sum of similarity
				simSums.setdefault(item,0)
				simSums[item] += sim
				
	#get the normalised list
	rankings = [(total/simSums[item],item) for item , total in totals.items()]

	#return the sorted list
	rankings.sort()
	rankings.reverse()
	return rankings
	
#def transform the people dictionary to movies
def transformPrefs(prefs):
	result = {}
	for person in prefs:
		for item in prefs[person]:
			result.setdefault(item,{})
			
			#flip the item and person
			result[item][person] = prefs[person][item]
	return result
	
# this is the end but additional categorisation and segmentation is needed