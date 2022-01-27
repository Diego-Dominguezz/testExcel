<?php

    class Paginator {
    
        private $_conn; // variable de conexión
        public $_limit; // Liimite de resultados
        public $_page; // Página actual
        private $_query; // SQL query
        public $_total; // Número de rows total
        public $numeroPaginas;

        public function __construct( $conn, $query, $limite = 10) {
            $this->_conn = $conn;
            $this->_query = $query;
            $rs= $this->_conn->query( $this->_query )or die(mysqli_error($db));
            $this->_total = $rs->num_rows;
            $this->_limit = $limite;
            $this->numeroPaginas = ceil($this->_total / $this->_limit);
        }

        public function getData( $page = 1 ) {
     
            $this->_page = $page;
        
            if ( $this->_limit == 'all' ) {
                $query = $this->_query;
            } else {
                $query = $this->_query . " LIMIT " . ( ( $this->_page - 1 ) * $this->_limit ) .", ".$this->_limit;
            }

            $rs = $this->_conn->query( $query )or die(mysqli_error($_conn));

            while ( $row = $rs->fetch_assoc() ) {
                $results[]  = $row;
            }
            
            $result         = new stdClass();
            $result->page   = $this->_page;
            $result->limit  = $this->_limit;
            $result->total  = $this->_total;
            $result->data   = $results;
        
            return $result;
        }
    
    }