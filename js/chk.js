                    //Implementing the “Remember me” option in login form using LocalStorage - localstorage.php WEEK13

                    function fetchLS(){
                        //function fetch
                        document.querySelector("#txtuname").value = localStorage.getItem("svem");
                        document.querySelector("#txtpsw").value = localStorage.getItem("svpwd");
                        }
                    
                        window.addEventListener("load", fetchLS); //when page loads
                    
                    function remem(){
                            var chk = document.querySelector("#chkrem");
                            if (chk.checked){
                                            var em = document.querySelector("#txtuname").value;
                                            var pwd = document.querySelector("#txtpsw").value;
                                            localStorage.setItem("svem", em);
                                            localStorage.setItem("svpwd", pwd);
                                            }
                    else{
                                                //uncheck
                                            localStorage.removeItem("svem");
                                            localStorage.removeItem("svpwd");
                                            }
                    }