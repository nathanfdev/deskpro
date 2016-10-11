import { Component } from 'react';

export class SeparateComponent extends Component {

  static getType() {
    throw new Error('You should implement getType method in derived class');
  }

}

