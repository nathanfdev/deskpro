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
        <li>
          <hr/>
        </li>
        <ViewField value="id" label="ID" isShown={fields.id.isShown} changeState={changeState}/>
        <ViewField value="hidden_status" label="Hidden status" isShown={fields.hidden_status.isShown}
                   changeState={changeState}/>
        <ViewField value="status_category" label="Status category" isShown={fields.status_category.isShown}
                   changeState={changeState}/>
        <ViewField value="custom_category" label="Category" isShown={fields.custom_category.isShown}
                   changeState={changeState}/>
        <ViewField value="date_created" label="Created" isShown={fields.date_created.isShown}
                   changeState={changeState}/>
        <ViewField value="total_rating" label="Rating" isShown={fields.total_rating.isShown} changeState={changeState}/>
        <ViewField value="num_rating" label="Votes" isShown={fields.num_rating.isShown} changeState={changeState}/>
        <ViewField value="num_comments" label="Comments" isShown={fields.num_comments.isShown}
                   changeState={changeState}/>
        <ViewField value="validating" label="Validating" isShown={fields.validating.isShown} changeState={changeState}/>
      </div>
    );
  }
}