const userIdentityPromise = fetch("api/get_user_identity_data.php")
	.then((response) => {
		if(!response.ok){
			throw new Error("Error fetching username!");
		}
		return response.json();
	})
	.catch(console.error);
