event: 
-   createUser
    1. (username, password) -> will create the user object from user and password
    2. (username, email) -> will create the user object from user and email
    3. (username) -> will create the user from username
- createUser.before

- form.submited
    - createClassified(formInput)
        - classified.created
            - save classifiedToDb
                - save ClassifiedToES
            - uploadImagesToQueue
            - send author email
            - send admin email
            - redirect to classified page
