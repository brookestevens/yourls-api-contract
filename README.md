# yourls-api-contract
Adds a contract API action. Opposite to the expand action, where one can see if a long url has been shortened without creating a new short link. Use action=contract and url=long_url_here to see if the URL has been shortened before. returns false if the URL hasn't been shortened bfore. Returns true and an array of sort links if it has been shortened before 
