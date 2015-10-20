import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {ViewField} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ViewField';

export class TableViewFieldsList extends Component {
  render() {
    return (
      <div>
        <ViewField value="id" label="ID" status={constants.FIELD_REQUIRED}/>
        <ViewField value="status" label="Status" status={constants.FIELD_REQUIRED}/>
        <ViewField value="hidden_status" label="Hidden status" status={constants.FIELD_REQUIRED}/>
        <ViewField value="title" label="Title" status={constants.FIELD_REQUIRED}/>
        <ViewField value="content" label="Content" status={constants.FIELD_REQUIRED}/>
        <ViewField value="status_category" label="Status category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="custom_category" label="Category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="author_name" label="Submitter" status={constants.FIELD_REQUIRED}/>
        <li>
          <hr/>
        </li>
        <ViewField value="language_id" label="Lang" status={constants.FIELD_REQUIRED}/>
        <ViewField value="type" label="Type" status={constants.FIELD_REQUIRED}/>
        <ViewField value="slug" label="Slug" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_created" label="Created" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_published" label="Published" status={constants.FIELD_REQUIRED}/>
        <ViewField value="view_count" label="Views" status={constants.FIELD_REQUIRED}/>
        <ViewField value="total_rating" label="Rating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_rating" label="Votes" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_comments" label="Comments" status={constants.FIELD_REQUIRED}/>
        <ViewField value="validating" label="Validating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="popularity" label="Popularity" status={constants.FIELD_REQUIRED}/>
      </div>
    );
  }
}

