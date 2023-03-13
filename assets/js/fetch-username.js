const usernamePromise = fetch("api/get-username.php")
	.then((response) => {
		if(!response.ok){
			throw new Error("Error fetching username!");
		}
		return response.json();
	})
	.catch(console.error);
