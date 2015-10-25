import React, {Component, PropTypes} from 'react';
import {ViewField} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ViewField';

export class CardViewFieldsList extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    changeState: PropTypes.func.isRequired,
    fields: PropTypes.object.isRequired
  };

  render() {
    const {changeState, fields} = this.props;
    return (
      <div>
        <ViewField value="status" label="Status" isShown fixed/>
        <ViewField value="title" label="Title" isShown fixed/>
        <ViewField value="type" label="Type" isShown fixed/>
        <ViewField value="content" label="Content" isShown fixed/>
        <ViewField value="author_name" label="Submitter" isShown fixed/>
        <ViewField value="num_comments" label="Comments" isShown fixed/>
        <ViewField value="total_rating" label="Rating" isShown fixed/>
        <ViewField value="num_ratings" label="Votes" isShown fixed/>
        <li>
          <hr/>
        </li>
        <ViewField value="id" label="ID" isShown={fields.id.isShown} changeState={changeState}/>
        <ViewField value="custom_category" label="Category" isShown={fields.custom_category.isShown}
                   changeState={changeState}/>
        <ViewField value="date_created" label="Created" isShown={fields.date_created.isShown}
                   changeState={changeState}/>
      </div>
    );
  }
}