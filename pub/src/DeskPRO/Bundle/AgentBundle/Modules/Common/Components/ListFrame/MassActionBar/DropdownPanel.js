import React, { Component, PropTypes } from 'react';
import {QuickFilter} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import { RadioChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

export class DropdownPanel extends Component {
  static propTypes = {
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object,
    item: PropTypes.object.isRequired
  };

  render() {
    const { item, setParams, currentParams, resetSingleAction } = this.props;

    const renderNested = (nested) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, index1) =>
              <RadioChoiceMenuOption key={index1}
                                     isActive={currentParams && currentParams.get(option.param) === option.value}
                                     value={option.value}
                                     param={option.param}
                                     label={option.label}
                                     resetSingleAction={resetSingleAction}
                                     setParams={setParams}/>
          )}
        </ul>
      );
    };

    return (
      <div className="dpw-navigation-dropdown-panel" style={{width: '250px'}}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <QuickFilter/>
            </div>
          </div>
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw--popup-item-collection">
                <ul>
                  {item.options.map((option, index) =>
                      <RadioChoiceMenuOption key={index}
                                             isActive={currentParams && currentParams.get(item.param) === option.value}
                                             value={option.value}
                                             label={option.label}
                                             param={item.param}
                                             resetSingleAction={resetSingleAction}
                                             setParams={setParams}>
                        {renderNested(option.nested)}
                      </RadioChoiceMenuOption>
                  )}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}