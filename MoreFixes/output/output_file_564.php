    public function toArray()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'user' => $this->user->getUsername(),
        ];
    }
