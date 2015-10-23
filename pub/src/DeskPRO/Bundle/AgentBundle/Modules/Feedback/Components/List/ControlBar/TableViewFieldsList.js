import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {ViewField} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ViewField';

export class TableViewFieldsList extends Component {
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
        <ViewField value="id" label="ID" isShown={fields.id.isShown} changeState={changeState}/>
        <ViewField value="hidden_status" label="Hidden status" isShown={fields.hidden_status.isShown}
                   changeState={changeState}/>
        <ViewField value="title" label="Title" isShown={fields.title.isShown} changeState={changeState}/>
        <ViewField value="content" label="Content" isShown={fields.content.isShown} changeState={changeState}/>
        <ViewField value="status_category" label="Status" isShown={fields.status_category.isShown}
                   changeState={changeState}/>
        <ViewField value="custom_category" label="Category" isShown={fields.custom_category.isShown}
                   changeState={changeState}/>
        <ViewField value="author_name" label="Submitter" isShown={fields.author_name.isShown} changeState={changeState}/>
        <ViewField value="type" label="Type" isShown={fields.type.isShown} changeState={changeState}/>
        <ViewField value="date_created" label="Created" isShown={fields.date_created.isShown}
                   changeState={changeState}/>
        <ViewField value="total_rating" label="Rating" isShown={fields.total_rating.isShown} changeState={changeState}/>
        <ViewField value="num_ratings" label="Votes" isShown={fields.num_ratings.isShown} changeState={changeState}/>
        <ViewField value="num_comments" label="Comments" isShown={fields.num_comments.isShown}
                   changeState={changeState}/>
      </div>
    );
  }
}

