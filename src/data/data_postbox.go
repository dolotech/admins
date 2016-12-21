package data

import (
	"basic/ssdb/gossdb"
	"encoding/json"
	"errors"
	"strconv"
	"time"
)

type DataPostbox struct {
	Id           uint32
	Title        string
	Appendixname string
	Appendix     []*WidgetData //
	Content      string
	Sender       string
	Receiver     string
	Createtime   uint32 //
	Read         bool   //
	Expire       uint32 //
	Kind         uint32 //
	Draw         bool   //
	Status       uint32
}

func (this *DataPostbox) ReadPost() error {
	err := this.Get()
	if err == nil {
		this.Status = 1
		err = this.Save()
	}
	return err
}

func (this *DataPostbox) Get() error {
	value, err := gossdb.C().Hget(KEY_POSTBOX+this.Receiver, strconv.Itoa(int(this.Id)))
	if err == nil {
		err = json.Unmarshal([]byte(value), this)
	}
	return err
}

func (this *DataPostbox) Save() error {
	size, err := gossdb.C().Hsize(KEY_POSTBOX + this.Receiver)
	if err != nil {
		return err
	}
	this.Id = uint32(size)
	this.Createtime = uint32(time.Now().Unix())
	return gossdb.C().Hset(KEY_POSTBOX+this.Receiver, strconv.Itoa(int(this.Id)), this)
}

// Delete delete an email
func (this *DataPostbox) Delete() error {
	return gossdb.C().MultiHdel(KEY_POSTBOX+this.Receiver, strconv.Itoa(int(this.Id)))
}

// Cleanup cleanup all emails
func (this *DataPostbox) Cleanup() error {
	return gossdb.C().Hclear(KEY_POSTBOX + this.Receiver)
}

// CleanupRead cleanup all readed emails
func (this *DataPostbox) CleanupRead() error {
	data, err := gossdb.C().MultiHgetAll(KEY_POSTBOX_LIST + this.Receiver)
	if err == nil {
		for _, v := range data {
			d := &DataPostbox{}
			err = json.Unmarshal([]byte(v), d)
			if err == nil {
				if d.Read == true {
					err = d.Delete()
				}
			}
		}
	}

	return err
}

func (this *DataPostbox) ReadAll() ([]*DataPostbox, error) {
	list := make([]*DataPostbox, 0)
	data, err := gossdb.C().MultiHgetAll(KEY_POSTBOX_LIST + this.Receiver)
	if err == nil {
		for _, v := range data {
			d := &DataPostbox{}
			err = json.Unmarshal([]byte(v), d)

			if err == nil {
				list = append(list, d)
			}
		}
	}

	return list, nil
}

func (this *DataPostbox) OpenAppendix() ([]*WidgetData, error) {
	err := this.Get()
	if err != nil {

	} else {
		if len(this.Appendix) > 0 {
			this.Draw = true
			err = this.Save()
		} else {
			err = errors.New("appendix is empty")
		}
	}
	return this.Appendix, err
}
