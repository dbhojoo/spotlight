import csv

new_data_dict = {}
with open("preferences_data.csv", 'r') as data_file:
    data = csv.DictReader(data_file, delimiter=",")
    for row in data:
        item = new_data_dict.get(row["user_id"], dict())
        item[row["event_type"]] = int(row["rating"])

        new_data_dict[row["user_id"]] = item

print new_data_dict