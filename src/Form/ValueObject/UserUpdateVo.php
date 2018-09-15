<?php
namespace App\Form\ValueObject;

class UserUpdateVo
{
    /**
     * @var string
     */
    protected $username;
    
    /**
     * @var string
     */
    protected $email;
    
    /**
     * @var string
     */
    protected $password;
    
    /**
     * @var string
     */
    protected $confirmpassword;
    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getConfirmpassword()
    {
        return $this->confirmpassword;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $confirmpassword
     */
    public function setConfirmpassword($confirmpassword)
    {
        $this->confirmpassword = $confirmpassword;
    }

    
    
}

