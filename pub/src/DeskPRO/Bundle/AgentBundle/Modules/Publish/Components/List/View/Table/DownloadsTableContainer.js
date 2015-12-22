import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId, PersonInTable }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';
import { peopleSelector }
  from '../../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => {
  return {
    people: peopleSelector(state),
    downloads: state.Publish.list.get('downloads')
  };
})

@injectIntl
export class DownloadsTableContainer extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired
  };

  render() {
    const {downloads, people} = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id" title="ID" visible/>
          <Th sort="author_name" title="Author" visible/>
          <Th sort="date_created" title="Created" visible/>
          <Th sort="date_updated" title="Updated" visible/>
          <Th sort="status" title="Status" visible/>
          <Th title="Labels" visible/>
          <Th sort="title" title="Title" visible/>
        </tr>
        </thead>
        <tbody>
        {downloads.map((element, index) =>
            <tr key={index}>
              <TdId visible>
                {element.id}
              </TdId>
              <Td visible>
                <PersonInTable person={people.get(element.person)}/>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_created}/></div>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={element.date_updated}/></div>
              </Td>
              <Td visible>
                {element.status}
              </Td>
              <Td visible>
                @ToDo some labels stuff
              </Td>
              <Td className="item-title" visible>
                <a href="#"><SlicedString string={element.title}/></a>
              </Td>
            </tr>
        )
        }
        </tbody>
      </Table>
    );
  }
}
