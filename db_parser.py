# Open mysql database
# Read all tags
# Read all mapped prompts
# Iterate over all records, binning results into each raw array (sums, counts)
# save as separate .npy files

import mysql.connector

cnx = mysql.connector.connect(user='root', password='',
                              host='127.0.0.1',
                              database='world_vote')
cursor = cnx.cursor()
query = ("SELECT slug FROM tags")
cursor.execute(query)
tag_slugs = [slug for slug in cursor]
print(tag_slugs)

cursor = cnx.cursor()
query = ("SELECT id FROM prompts WHERE is_mapped = 1")
cursor.execute(query)
prompt_ids = [p_id for p_id in cursor]
print(prompt_ids)

cursor = cnx.cursor()
query = ("SELECT grid_row, grid_col, tags, responses FROM users")
cursor.execute(query)
for grid_row, grid_col, tags, responses in cursor:
    # Parse into arrays
    print(grid_row, grid_col, tags, responses)

cursor.close()
cnx.close()
