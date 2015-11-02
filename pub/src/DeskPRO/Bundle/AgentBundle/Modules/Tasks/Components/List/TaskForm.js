import React from 'react';
import Formsy from 'formsy-react';

export class TaskForm extends React.Component {

  render() {
    return (
      <Formsy.Form onSubmit={()=>{}}>
        <input name="title" type="text"/>
        <button type="submit" value="Save" className="button">Add</button>
      </Formsy.Form>
    );
  }
}
