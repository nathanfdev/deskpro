import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {ViewField} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ViewField';

export class CardViewFieldsList extends Component {
  render() {
    const {changeState} = this.props;
    return (
      <div>
        <ViewField value="status" label="Status" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="title" label="Title" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="type" label="Type" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="content" label="Content" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="author_name" label="Submitter" status={constants.FIELD_REQUIRED} fixed/>
        <li>
          <hr/>
        </li>
        <ViewField value="id" label="ID" status={constants.FIELD_SHOWN} changeState={changeState}/>
        <ViewField value="hidden_status" label="Hidden status" status={constants.FIELD_SHOWN}
                   changeState={changeState}/>
        <ViewField value="status_category" label="Status category" status={constants.FIELD_SHOWN}
                   changeState={changeState}/>
        <ViewField value="custom_category" label="Category" status={constants.FIELD_SHOWN}/>
        <ViewField value="language_id" label="Lang" status={constants.FIELD_SHOWN}/>
        <ViewField value="slug" label="Slug" status={constants.FIELD_SHOWN}/>
        <ViewField value="date_created" label="Created" status={constants.FIELD_SHOWN}/>
        <ViewField value="date_published" label="Published" status={constants.FIELD_SHOWN}/>
        <ViewField value="view_count" label="Views" status={constants.FIELD_SHOWN}/>
        <ViewField value="total_rating" label="Rating" status={constants.FIELD_SHOWN}/>
        <ViewField value="num_rating" label="Votes" status={constants.FIELD_SHOWN}/>
        <ViewField value="num_comments" label="Comments" status={constants.FIELD_SHOWN}/>
        <ViewField value="validating" label="Validating" status={constants.FIELD_SHOWN}/>
        <ViewField value="popularity" label="Popularity" status={constants.FIELD_SHOWN}/>
      </div>
    );
  }
}