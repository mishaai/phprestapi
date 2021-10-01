<?php
        class API {

            private $db;
            private $requestMethod;
            private $data;

            function __construct($requestMethod,$data) 
            {

                $this->DB = New DB();
                $this->requestMethod = $requestMethod;
                $this->data = $data;         

            }

            public function processRequest()
            {
                switch ($this->requestMethod) {
                case 'GET':
                    if (!empty($this->data->customerId))  {
                    $response = $this->getCustomer($this->data->customerId);
                    } else {
                    $response = $this->getAllCustomers();
                    };
                    break;
                case 'POST':
                    $response = $this->createCustomer();
                    break;
                case 'PUT':
                    isset($this->data->customerId) ?$response = $this->updateCustomer($this->data->customerId):$response = $this->unprocessableEntityResponse();
                    break;
                case 'DELETE':
                    isset($this->data->customerId) ? $response = $this->deleteCustomer($this->data->customerId):$response = $this->unprocessableEntityResponse();
                    break;
                default:
                    $response = $this->notFoundResponse();
                    break;

                }
                header($response['status_code_header']);
                if ($response['body']) {
                    echo $response['body'];
                }
            }
            /**
             * find function  by ID
             */
            public function find($id)
            {
                $query = "
                SELECT
                    *
                FROM
                `customer`
                WHERE id = :id;
                ";

                try {
                $conn= $this->DB->getConnection(); 
                $statement = $conn->prepare($query);
                $statement->execute(array('id' => $id));
                $result = $statement->fetch(\PDO::FETCH_ASSOC);
                return $result;
                } catch (\PDOException $e) {
                exit($e->getMessage());
                }
            }
            /**
             * get customer by id
             */
            private function getCustomer($id)
            {
                $result = $this->find($id);
                if (! $result) {
                    return $this->notFoundResponse();
                }
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode($result);
                return $response;
            }
            /** get all customers 
             * 
            */
            private function getAllCustomers() 
            {
                $query = "
                SELECT
                    *
                FROM
                `customer`
                ";

                try {
                $conn= $this->DB->getConnection();
                $statement = $conn->prepare($query);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                exit($e->getMessage());
                }

                $response['status_code_header'] = 'HTTP/1.1 200 OK';
               // print_r( $result);
                $response['body'] = json_encode($result);
                return $response;
            }
            /** 
             * delete customer by id
            */
            private function deleteCustomer($id)
            {
                $result = $this->find($id);
                if (! $result) {
                return $this->notFoundResponse();
                }

                $query = "
                DELETE FROM `customer`
                WHERE id = :id;
                ";

                try {
                $conn= $this->DB->getConnection();
                $statement = $conn->prepare($query);
                $statement->execute(array('id' => $id));
                $statement->rowCount();
                } catch (\PDOException $e) {
                exit($e->getMessage());
                }
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode(array('message' => 'Customer Deleted!'));
                return $response;
            }
            /** 
             * customer create
             * 
             */
            private function createCustomer() 
            {
               
                if (! $this->validatePost($this->data)) {
                return $this->unprocessableEntityResponse();
                }

                $query = "
                INSERT INTO `customer`
                    (firstName, lastName, emaill, phone,address)
                VALUES
                    (:firstName, :lastName, :emaill, :phone,:address);
                ";

                try {
                $conn= $this->DB->getConnection();
                $statement = $conn->prepare($query);
                $statement->execute(array(
                    'firstName' => $this->data->firstName,
                    'lastName'  => $this->data->lastName,
                    'emaill' => $this->data->emaill,
                    'phone' => $this->data->phone,
                    'address' => $this->data->address,
                    
                ));
                $statement->rowCount();
                } catch (\PDOException $e) {
                exit($e->getMessage());
                }

                $response['status_code_header'] = 'HTTP/1.1 201 Created';
                $response['body'] = json_encode(array('message' => 'Customer Created'));
                return $response;
            }
            /** 
             * customer update
             * 
             */
            private function updateCustomer($id)
            {
                $result = $this->find($id);
                if (! $result) {
                return $this->notFoundResponse();
                }
                if (! $this->validatePost($this->data)) {
                return $this->unprocessableEntityResponse();
                }

                $statement = "
                UPDATE `customer`
                SET
                    firstName = :firstName,
                    lastName  = :lastName,
                    emaill = :emaill,
                    phone = :phone,
                    address = :address
                WHERE id = :id;
                ";

                try {
                $conn= $this->DB->getConnection();
                $statement = $conn->prepare($statement);
                $statement->execute(array(
                    'id' => (int) $id,
                    'firstName' => $this->data->firstName,
                    'lastName'  => $this->data->lastName,
                    'emaill' => $this->data->emaill,
                    'phone' => $this->data->phone,
                    'address' => $this->data->address
                ));
                $statement->rowCount();
                } catch (\PDOException $e) {
                exit($e->getMessage());
                }
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode(array('message' => 'Customer Updated!'));
                return $response;
            }
            /**
             *  Not found response   
             * 
            */
            private function notFoundResponse()
            {
                $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
                $response['body'] = json_encode(array('message' => 'Customer  Not Found'));
                return $response;
            }
            /**
             * Unprocessable Entity response 
             * 
             * */
            private function unprocessableEntityResponse()
            {
                $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
                $response['body'] = json_encode([
                'error' => 'Invalid input'
                ]);
                return $response;
            }
            /** 
             * post field validator 
             * */
            private function validatePost($input)
            {
              if (! isset($input->firstName)) {
                return false;
              }
              if (! isset($input->lastName)) {
                return false;
              }
              if (! isset($input->emaill)) {
                return false;
              }
              if (! isset($input->phone)) {
                return false;
              }
              if (! isset($input->address)) {
                return false;
              }
          
              return true;
            }

   }

?>